<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model;

use Amasty\CompanyAccount\Api\Data\CompanyInterface;
use Amasty\CompanyAccount\Api\Data\CreditEventInterface;
use Amasty\CompanyAccount\Api\Data\CreditInterface;
use Amasty\CompanyAccount\Model\Credit\Event\Comment\Constants;
use Amasty\CompanyAccount\Model\Credit\Event\Comment\GetValue as GetEventCommentValue;
use Amasty\CompanyAccount\Model\Credit\Event\RetrieveAmount as RetrieveEventAmount;
use Amasty\CompanyAccount\Model\Credit\Overdraft\CalculateRepayDate;
use Amasty\CompanyAccount\Model\Credit\Overdraft\Query\GetByCreditIdInterface;
use Amasty\CompanyAccount\Model\Price\Format as PriceFormat;
use Amasty\CompanyAccount\Model\Source\Company\Status;
use IntlDateFormatter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class MailManager
{
    public const LINK_CUSTOMER = 'link';
    public const DISABLE_CUSTOMER = 'disable';

    /**
     * @var MailSender
     */
    private $mailSender;

    /**
     * @var PriceFormat
     */
    private $priceFormat;

    /**
     * @var RetrieveEventAmount
     */
    private $retrieveEventAmount;

    /**
     * @var GetEventCommentValue
     */
    private $getEventCommentValue;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var GetByCreditIdInterface
     */
    private $getByCreditId;

    /**
     * @var CalculateRepayDate
     */
    private $calculateRepayDate;

    public function __construct(
        MailSender $mailSender,
        PriceFormat $priceFormat,
        RetrieveEventAmount $retrieveEventAmount,
        GetEventCommentValue $getEventCommentValue,
        TimezoneInterface $timezone,
        GetByCreditIdInterface $getByCreditId,
        CalculateRepayDate $calculateRepayDate
    ) {
        $this->mailSender = $mailSender;
        $this->priceFormat = $priceFormat;
        $this->retrieveEventAmount = $retrieveEventAmount;
        $this->getEventCommentValue = $getEventCommentValue;
        $this->timezone = $timezone;
        $this->getByCreditId = $getByCreditId;
        $this->calculateRepayDate = $calculateRepayDate;
    }

    /**
     * @param array $customerData
     * @param string $action
     * @throws LocalizedException
     */
    public function sendCustomerEmails(array $customerData, string $action)
    {
        switch ($action) {
            case self::LINK_CUSTOMER:
                $this->mailSender->sendLinkCustomerToCompanyNotification($customerData);
                break;
            case self::DISABLE_CUSTOMER:
                $this->mailSender->sendDisableCustomerNotification($customerData);
                break;
        }
    }

    /**
     * @param bool $isEdited
     * @param CompanyInterface $newCompany
     * @param CompanyInterface $oldCompany
     * @throws LocalizedException
     */
    public function sendCompanyEmails(bool $isEdited, CompanyInterface $newCompany, CompanyInterface $oldCompany)
    {
        if (!$isEdited) {
            $this->sendCreateCompanyEmail($newCompany);
        }

        if (!$isEdited || $this->isRepresentativeChanged($newCompany, $oldCompany)) {
            $this->mailSender->sendRepresentativeSetNotification($newCompany);
        }

        if (!$isEdited || $this->isStatusChanged($newCompany, $oldCompany)) {
            $this->sendChangeStatusEmail($newCompany);
        }

        if ($this->isAdminChanged($newCompany, $oldCompany)) {
            $this->mailSender->sendChangeAdminEmail($newCompany, $oldCompany);
        }

        if ($this->isOverdraftChanged($newCompany)) {
            $this->mailSender->sendOverdraftChanged($newCompany);
        }
    }

    /**
     * @param int $companyId
     * @param CreditEventInterface $creditEvent
     * @return void
     * @throws LocalizedException
     */
    public function sendCreditChangesByAdmin(int $companyId, CreditEventInterface $creditEvent): void
    {
        $amount = $this->retrieveEventAmount->execute($creditEvent);
        $formattedAmount = $this->priceFormat->execute(
            $amount,
            $creditEvent->getCurrencyEvent()
        );

        $data = [
            'amount' => $amount > 0 ? sprintf('+%s', $formattedAmount) : $formattedAmount,
            'balance' => $this->priceFormat->execute($creditEvent->getBalance(), $creditEvent->getCurrencyCredit()),
            'comment' => $this->getEventCommentValue->execute($creditEvent, Constants::COMMENT)
        ];

        $this->mailSender->sendCreditChangesByAdmin($companyId, $data);
    }

    /**
     * @param CreditInterface $credit
     * @return void
     * @throws LocalizedException
     */
    public function sendOverdraftUsed(CreditInterface $credit): void
    {
        try {
            $overdraft = $this->getByCreditId->execute((int)$credit->getId());
            $repayDate = $overdraft->getRepayDate();
        } catch (NoSuchEntityException $e) {
            $repayDate = $this->calculateRepayDate->execute($credit);
        }
        $repayDate = $this->timezone->formatDateTime(
            $repayDate,
            IntlDateFormatter::MEDIUM
        );

        $this->mailSender->sendOverdraftUsed($credit->getCompanyId(), [
            'repay_date' => $repayDate
        ]);
    }

    /**
     * @param CreditInterface $credit
     * @return void
     * @throws LocalizedException
     */
    public function sendOverdraftPenalty(CreditInterface $credit): void
    {
        $this->mailSender->sendOverdraftPenalty($credit->getCompanyId(), [
            'penalty' => $credit->getOverdraftPenalty()
        ]);
    }

    /**
     * @param CompanyInterface $company
     * @throws LocalizedException
     */
    private function sendCreateCompanyEmail(CompanyInterface $company)
    {
        if ($company->getStatus() == Status::STATUS_PENDING) {
            $templatePath = ConfigProvider::ADMIN_NOTIF_NEW_COMPANY_REQUEST;
        } else {
            $templatePath = ConfigProvider::ADMIN_NOTIF_NEW_COMPANY_CREATE;
        }

        $this->mailSender->sendCompanyCreateNotification($templatePath, $company);
    }

    /**
     * @param CompanyInterface $company
     * @throws LocalizedException
     */
    private function sendChangeStatusEmail(CompanyInterface $company)
    {
        switch ($company->getStatus()) {
            case Status::STATUS_ACTIVE:
                $templatePath = ConfigProvider::CUSTOMER_NOTIF_ACTIVE_STATUS;
                break;
            case Status::STATUS_INACTIVE:
                $templatePath = ConfigProvider::CUSTOMER_NOTIF_INACTIVE_STATUS;
                break;
            case Status::STATUS_REJECTED:
                $templatePath = ConfigProvider::CUSTOMER_NOTIF_REJECTED_STATUS;
                break;
            default:
                $templatePath = '';
        }

        $this->mailSender->sendChangeStatusNotification($templatePath, $company);
    }

    /**
     * @param CompanyInterface $newCompany
     * @param CompanyInterface $oldCompany
     * @return bool
     */
    private function isRepresentativeChanged(CompanyInterface $newCompany, CompanyInterface $oldCompany)
    {
        return $oldCompany->getSalesRepresentativeId() !== $newCompany->getSalesRepresentativeId();
    }

    /**
     * @param CompanyInterface $newCompany
     * @param CompanyInterface $oldCompany
     * @return bool
     */
    private function isStatusChanged(CompanyInterface $newCompany, CompanyInterface $oldCompany)
    {
        return $newCompany->getStatus() !== $oldCompany->getStatus();
    }

    /**
     * @param CompanyInterface $newCompany
     * @param CompanyInterface $oldCompany
     * @return bool
     */
    private function isAdminChanged(CompanyInterface $newCompany, CompanyInterface $oldCompany)
    {
        return $newCompany->getSuperUserId() !== $oldCompany->getSuperUserId();
    }

    private function isOverdraftChanged(CompanyInterface $company): bool
    {
        $credit = $company->getExtensionAttributes()->getCredit();

        if (!$credit) {
            return false;
        }

        return $credit->dataHasChangedFor(CreditInterface::ALLOW_OVERDRAFT)
            || $credit->dataHasChangedFor(CreditInterface::OVERDRAFT_LIMIT)
            || $credit->dataHasChangedFor(CreditInterface::OVERDRAFT_REPAY_PERIOD)
            || $credit->dataHasChangedFor(CreditInterface::OVERDRAFT_REPAY_TYPE)
            || $credit->dataHasChangedFor(CreditInterface::OVERDRAFT_REPAY_DIGIT)
            || $credit->dataHasChangedFor(CreditInterface::OVERDRAFT_PENALTY);
    }
}

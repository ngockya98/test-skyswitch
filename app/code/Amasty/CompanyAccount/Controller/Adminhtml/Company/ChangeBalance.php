<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Controller\Adminhtml\Company;

use Amasty\CompanyAccount\Api\CompanyRepositoryInterface;
use Amasty\CompanyAccount\Api\Data\CompanyInterface;
use Amasty\CompanyAccount\Api\Data\CreditInterface;
use Amasty\CompanyAccount\Model\Backend\Company\Registry as CompanyRegistry;
use Amasty\CompanyAccount\Model\Backend\CreditEvent\Get as GetCreditEvent;
use Amasty\CompanyAccount\Model\Credit\AppendCreditEvent;
use Amasty\CompanyAccount\Model\Price\Format as FormatPrice;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Validation\ValidationException;

class ChangeBalance extends Action
{
    public const CREDIT_EVENT_PARAM = 'credit_event';

    /**
     * @var CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @var CompanyRegistry
     */
    private $companyRegistry;

    /**
     * @var GetCreditEvent
     */
    private $getCreditEvent;

    /**
     * @var AppendCreditEvent
     */
    private $appendCreditEvent;

    /**
     * @var FormatPrice
     */
    private $formatPrice;

    public function __construct(
        CompanyRepositoryInterface $companyRepository,
        CompanyRegistry $companyRegistry,
        GetCreditEvent $getCreditEvent,
        AppendCreditEvent $appendCreditEvent,
        FormatPrice $formatPrice,
        Context $context
    ) {
        parent::__construct($context);
        $this->companyRepository = $companyRepository;
        $this->companyRegistry = $companyRegistry;
        $this->getCreditEvent = $getCreditEvent;
        $this->appendCreditEvent = $appendCreditEvent;
        $this->formatPrice = $formatPrice;
    }

    /**
     * @return Json
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        try {
            $company = $this->companyRepository->getById(
                (int) $this->getRequest()->getParam(CompanyInterface::COMPANY_ID),
                true
            );
            $this->companyRegistry->set($company);
        } catch (NoSuchEntityException $e) {
            return $resultJson->setData([
                'errors' => [$e->getMessage()]
            ]);
        }

        try {
            $creditEvent = $this->getCreditEvent->execute(
                $this->getRequest()->getParam(self::CREDIT_EVENT_PARAM, [])
            );
            $this->appendCreditEvent->execute(
                $company->getExtensionAttributes()->getCredit(),
                [$creditEvent]
            );
        } catch (ValidationException $e) {
            $errors = [];
            foreach ($e->getErrors() as $error) {
                $errors[] = $error->getMessage();
            }
            return $resultJson->setData(['errors' => $errors]);
        } catch (LocalizedException $e) {
            return $resultJson->setData([
                'errors' => [$e->getMessage()]
            ]);
        }

        $credit = $company->getExtensionAttributes()->getCredit();
        return $resultJson->setData([
            CreditInterface::BALANCE => $this->formatPrice($credit->getBalance(), $credit->getCurrencyCode()),
            CreditInterface::ISSUED_CREDIT => $this->formatPrice(
                $credit->getIssuedCredit(),
                $credit->getCurrencyCode()
            ),
            CreditInterface::BE_PAID => $this->formatPrice($credit->getBePaid(), $credit->getCurrencyCode())
        ]);
    }

    protected function formatPrice(float $price, ?string $currencyCode): string
    {
        return $this->formatPrice->execute($price, $currencyCode);
    }
}

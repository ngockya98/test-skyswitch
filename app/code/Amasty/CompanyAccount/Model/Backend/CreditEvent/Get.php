<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Backend\CreditEvent;

use Amasty\CompanyAccount\Api\Data\CreditEventInterface;
use Amasty\CompanyAccount\Model\Backend\Credit\GetCurrency as GetCreditCurrency;
use Amasty\CompanyAccount\Model\Credit\Event\Comment\Constants as CommentConstants;
use Amasty\CompanyAccount\Model\Credit\Event\Query\CreateCreditEventInterface;
use Amasty\CompanyAccount\Model\WebsiteCurrency;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Validation\ValidationException;
use Magento\Framework\Validation\ValidationResultFactory;

class Get
{
    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @var GetCreditCurrency
     */
    private $getCreditCurrency;

    /**
     * @var WebsiteCurrency
     */
    private $websiteCurrency;

    /**
     * @var array
     */
    private $requiredFields;

    /**
     * @var CreateCreditEventInterface
     */
    private $createCreditEvent;

    /**
     * @var FormatInterface
     */
    private $formatNumber;

    public function __construct(
        ValidationResultFactory $validationResultFactory,
        GetCreditCurrency $getCreditCurrency,
        CreateCreditEventInterface $createCreditEvent,
        WebsiteCurrency $websiteCurrency,
        FormatInterface $formatNumber,
        array $requiredFields = []
    ) {
        $this->validationResultFactory = $validationResultFactory;
        $this->getCreditCurrency = $getCreditCurrency;
        $this->websiteCurrency = $websiteCurrency;
        $this->requiredFields = $requiredFields;
        $this->createCreditEvent = $createCreditEvent;
        $this->formatNumber = $formatNumber;
    }

    /**
     * Retrieve credit event object based on request params.
     *
     * @param array $params
     * @return CreditEventInterface
     * @throws ValidationException
     * @throws LocalizedException
     */
    public function execute(array $params): CreditEventInterface
    {
        $this->validateForRequired($params);

        $data[CreditEventInterface::AMOUNT] = (float) $this->formatNumber->getNumber(
            $params[CreditEventInterface::AMOUNT]
        );
        $data[CreditEventInterface::TYPE] = $params[CreditEventInterface::TYPE];
        $data[CreditEventInterface::COMMENT] = isset($params[CreditEventInterface::COMMENT])
            ? [CommentConstants::COMMENT => $params[CreditEventInterface::COMMENT]]
            : null;
        $data[CreditEventInterface::CURRENCY_CREDIT] = $this->getCreditCurrency->execute();
        if (isset($params[CreditEventInterface::CURRENCY_EVENT])
            && $this->websiteCurrency->isCreditCurrencyEnabled($params[CreditEventInterface::CURRENCY_EVENT])
        ) {
            $data[CreditEventInterface::CURRENCY_EVENT] = $params[CreditEventInterface::CURRENCY_EVENT];
        } else {
            $data[CreditEventInterface::CURRENCY_EVENT] = $data[CreditEventInterface::CURRENCY_CREDIT];
        }

        return $this->createCreditEvent->execute($data);
    }

    /**
     * @param array $params
     * @return void
     * @throws ValidationException
     */
    private function validateForRequired(array $params): void
    {
        $errors = [];

        foreach ($this->requiredFields as $requiredField) {
            if (!isset($params[$requiredField])) {
                $errors[] = __('The "%1" value doesn\'t exist. Enter the value and try again.', $requiredField);
            }
        }

        $validationResult = $this->validationResultFactory->create(['errors' => $errors]);
        if (!$validationResult->isValid()) {
            throw new ValidationException(__('Validation Failed'), null, 0, $validationResult);
        }
    }
}

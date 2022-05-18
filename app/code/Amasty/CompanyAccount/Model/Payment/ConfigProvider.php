<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Payment;

use Amasty\CompanyAccount\Api\OverdraftRepositoryInterface;
use Amasty\CompanyAccount\Model\CompanyContext;
use Amasty\CompanyAccount\Model\Price\Convert as PriceConvert;
use Amasty\CompanyAccount\Model\Price\Format as PriceFormat;
use Amasty\CompanyAccount\Model\WebsiteCurrency;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * Name for payment method.
     */
    public const METHOD_NAME = 'amasty_company_credit';

    public const ACL_RESOURCE = 'Amasty_CompanyAccount::use_credit';

    /**
     * @var CompanyContext
     */
    private $companyContext;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var PriceFormat
     */
    private $priceFormat;

    /**
     * @var OverdraftRepositoryInterface
     */
    private $overdraftRepository;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var CartInterface
     */
    private $quote;

    /**
     * @var PriceConvert
     */
    private $priceConvert;

    /**
     * @var WebsiteCurrency
     */
    private $websiteCurrency;

    public function __construct(
        CompanyContext $companyContext,
        UserContextInterface $userContext,
        PriceFormat $priceFormat,
        PriceConvert $priceConvert,
        OverdraftRepositoryInterface $overdraftRepository,
        CartRepositoryInterface $quoteRepository,
        RequestInterface $request,
        WebsiteCurrency $websiteCurrency
    ) {
        $this->companyContext = $companyContext;
        $this->userContext = $userContext;
        $this->priceFormat = $priceFormat;
        $this->overdraftRepository = $overdraftRepository;
        $this->quoteRepository = $quoteRepository;
        $this->request = $request;
        $this->priceConvert = $priceConvert;
        $this->websiteCurrency = $websiteCurrency;
    }

    /**
     * Get credit limit config.
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getConfig()
    {
        $config = [];

        $company = $this->companyContext->getCurrentCompany();
        if ($company && $company->getCompanyId()) {
            $quote = $this->getQuote();
            $credit = $company->getExtensionAttributes()->getCredit();
            $creditBalance = $credit->getBalance();
            if ($credit->isOverdraftAllowed()) {
                $creditBalance += $credit->getOverdraftLimit();
            }
            $creditCurrencyCode = $credit->getCurrencyCode()
                ?: $this->websiteCurrency->getCurrencyByCode()->getCurrencyCode();

            $config = [
                'balance' => $this->priceFormat->execute($credit->getBalance(), $creditCurrencyCode),
                'be_paid' => $this->priceFormat->execute($credit->getBePaid(), $creditCurrencyCode),
                'balance_quote_currency' => $this->priceConvert->execute(
                    $creditBalance,
                    $creditCurrencyCode,
                    $quote->getQuoteCurrencyCode()
                ),
                'currency' => $creditCurrencyCode,
                'isBaseCreditCurrencyRateEnabled' => $this->isBaseCreditCurrencyRateEnabled(
                    $quote,
                    $creditCurrencyCode
                ),
            ];

            if ($credit->isOverdraftAllowed()) {
                $config['overdraft_limit'] = $this->priceFormat->execute(
                    $credit->getOverdraftLimit(),
                    $creditCurrencyCode
                );
            }

            if ($this->overdraftRepository->isExistForCredit((int) $credit->getId())) {
                $config['overdraft'] = [
                    'exceed' => $this->overdraftRepository->isOverdraftExceed((int) $credit->getId()),
                    'repay_date' => $this->overdraftRepository->getByCreditId((int) $credit->getId())->getRepayDate(),
                    'penalty' => $credit->getOverdraftPenalty()
                ];
            }
        }

        return [
            'payment' => [
                'amasty_company_credit' => $config
            ]
        ];
    }

    /**
     * @return CartInterface
     * @throws NoSuchEntityException
     */
    private function getQuote(): CartInterface
    {
        if ($this->quote === null) {
            try {
                $this->quote = $this->quoteRepository->getActiveForCustomer($this->userContext->getUserId());
            } catch (NoSuchEntityException $e) {
                $id = $this->request->getParam('negotiableQuoteId');
                $this->quote = $this->quoteRepository->get($id);
            }
        }

        return $this->quote;
    }

    private function isBaseCreditCurrencyRateEnabled(CartInterface $quote, string $toCurrency): bool
    {
        return $this->websiteCurrency->isRateEnabled($quote->getQuoteCurrencyCode(), $toCurrency);
    }
}

<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Block;

use Amasty\CompanyAccount\Controller\Profile\SaveCompany;
use Amasty\CompanyAccount\Model\CompanyContext;
use Magento\Customer\Block\Account\SortLinkInterface;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Html\Link;
use Magento\Framework\View\Element\Template\Context as TemplateContext;

class TopLink extends Link implements SortLinkInterface
{
    /**
     * @var CompanyContext
     */
    private $companyContext;

    public function __construct(
        CompanyContext $companyContext,
        TemplateContext $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->companyContext = $companyContext;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->isAllowed()) {
            $result = parent::_toHtml();
        } else {
            $result = '';
        }

        return $result;
    }

    private function isAllowed(): bool
    {
        return $this->companyContext->isCurrentUserCompanyUser() || $this->companyContext->isCreateCompanyAllowed();
    }

    public function getHref(): string
    {
        return $this->getUrl(SaveCompany::AMASTY_COMPANY_PROFILE_INDEX);
    }

    public function getLabel(): Phrase
    {
        return __('Company Account');
    }

    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }
}

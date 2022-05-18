<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Block\Account;

use Amasty\CompanyAccount\Model\CompanyContext;
use Magento\Framework\View\Element\Template;

class OrderPermissionChecker extends \Magento\Framework\View\Element\Template
{
    public const RESOURCE = 'Amasty_CompanyAccount::orders_view';

    /**
     * @var CompanyContext
     */
    private $companyContext;

    public function __construct(
        Template\Context $context,
        CompanyContext $companyContext,
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
        $html = '';
        if ($this->companyContext->getCurrentCustomerId()
            && !$this->companyContext->isResourceAllow(self::RESOURCE)
        ) {
            $html = parent::_toHtml();
        }

        return $html;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCompanyAdminEmail()
    {
        $companyAdmin = $this->companyContext->getCurrentCompanyAdmin();

        return $companyAdmin ? $companyAdmin->getEmail() : '';
    }
}

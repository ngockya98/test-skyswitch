<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Block;

use Magento\Framework\App\DefaultPathInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use Amasty\CompanyAccount\Model\CompanyContext;
use Amasty\CompanyAccount\Block\Company\Profile;

class Link extends \Magento\Framework\View\Element\Html\Link\Current
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var CompanyContext
     */
    private $companyContext;

    public function __construct(
        Context $context,
        DefaultPathInterface $defaultPath,
        UrlInterface $urlBuilder,
        CompanyContext $companyContext,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $data);
        $this->urlBuilder = $urlBuilder;
        $this->companyContext = $companyContext;
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        return $this->companyContext->isCreateCompanyAllowed() ? parent::toHtml() : '';
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->urlBuilder->getUrl(Profile::AMASTY_COMPANY_PROFILE_CREATE);
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return __('Create a Company Account');
    }
}

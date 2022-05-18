<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Block\Link;

use Amasty\CompanyAccount\Model\Company\Role\Acl\IsAclShowed;
use Amasty\CompanyAccount\Model\ConfigProvider;
use Amasty\CompanyAccount\Model\UrlModifier;
use Magento\Framework\View\Element\Html\Link\Current;

class AbstractLink extends Current implements \Magento\Customer\Block\Account\SortLinkInterface
{
    /**
     * @var string
     */
    protected $resource;

    /**
     * @var \Amasty\CompanyAccount\Model\CompanyContext
     */
    protected $companyContext;

    /**
     * @var IsAclShowed
     */
    private $isAclShowed;

    /**
     * @var UrlModifier
     */
    private $urlModifier;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        \Amasty\CompanyAccount\Model\CompanyContext $companyContext,
        UrlModifier $urlModifier,
        IsAclShowed $isAclShowed,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $data);
        $this->companyContext = $companyContext;
        $this->resource = $data['resource'] ?? null;
        $this->isAclShowed = $isAclShowed;
        $this->urlModifier = $urlModifier;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        return $this->isAllowed() ? parent::_toHtml() : '';
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->urlModifier->modify(parent::getHref());
    }

    /**
     * @return bool
     */
    protected function isAllowed()
    {
        return $this->companyContext->isResourceAllow($this->resource) && $this->isAclShowed->execute($this->resource);
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }
}

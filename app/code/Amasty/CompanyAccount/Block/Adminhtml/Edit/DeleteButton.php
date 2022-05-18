<?php

namespace Amasty\CompanyAccount\Block\Adminhtml\Edit;

use Amasty\CompanyAccount\Api\Data\CompanyInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteButton implements ButtonProviderInterface
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
    }

    /**
     * @return \Magento\Framework\UrlInterface
     */
    public function getUrlBuilder()
    {
        return $this->urlBuilder;
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     *
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->urlBuilder->getUrl($route, $params);
    }

    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        $data = [];
        $companyId = $this->request->getParam(CompanyInterface::COMPANY_ID);
        if ($companyId) {
            $data = [
                'label'      => __('Delete Account'),
                'class'      => 'delete',
                'on_click'   => 'deleteConfirm(\'' . __(
                    'Are you sure you want to delete account? The action cannot be undone after confirmation. '
                    . 'The customer status of deleted company’s users will be set to Inactive.'
                ) . '\', \'' . $this->getUrlBuilder()->getUrl(
                    '*/*/delete',
                    [CompanyInterface::COMPANY_ID => $companyId]
                ) . '\')',
                'sort_order' => 20,
            ];
        }

        return $data;
    }
}

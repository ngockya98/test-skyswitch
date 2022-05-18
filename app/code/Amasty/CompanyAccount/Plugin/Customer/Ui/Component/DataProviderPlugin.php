<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Plugin\Customer\Ui\Component;

use Amasty\CompanyAccount\Controller\Adminhtml\Customer\MassAssign;
use Magento\Customer\Ui\Component\DataProvider as CustomerDataProvider;
use Magento\Framework\AuthorizationInterface;

class DataProviderPlugin
{
    public const GRID_NAME = 'customer_listing_data_source';

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Amasty\CompanyAccount\Model\Source\Company
     */
    private $companySource;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        \Amasty\CompanyAccount\Model\Source\Company $companySource,
        AuthorizationInterface $authorization
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->companySource = $companySource;
        $this->authorization = $authorization;
    }

    /**
     * @param CustomerDataProvider $subject
     * @param array $meta
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetMeta(CustomerDataProvider $subject, array $meta) : array
    {
        if ($subject->getName() === self::GRID_NAME) {
            $children = &$meta['listing_top']['children']['listing_massaction']['children'];
            if ($this->authorization->isAllowed(MassAssign::ADMIN_RESOURCE)) {
                $children['assign_company'] = $this->generateComponent();
            }

            if (!$children) {
                $children = [];
            }
        }

        return $meta;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function generateComponent() : array
    {
        $data = [
            'name' => 'assign_company',
            'confirm_title' => __('Assign to Company Account')->render(),
            'confirm_message' => __('Are you sure you want to assign company to selected items?')->render(),
            'label' => __('Assign to Company Account')->render(),
            'fieldLabel' => __('Assign to')->render(),
            'url' => $this->urlBuilder->getUrl('amcompany/customer/massAssign'),
        ];

        return $this->generateElement($data);
    }

    /**
     * @param array $data
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function generateElement(array $data) : array
    {
        $result = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'component' => 'uiComponent',
                        'componentType' => 'action',
                        'amasty_actions' => 'true',
                        'type' => 'amasty_' . $data['name'],
                        'label' => $data['label'],
                        'url' => $data['url'],
                        'confirm' => [
                            'title' => $data['confirm_title'],
                            'message' => $data['confirm_message'],
                        ],
                    ]
                ],
                'actions' => [
                    0 => [
                        'fieldLabel' => $data['fieldLabel'],
                        'url' => $data['url'],
                        'type' => 'amasty_' . $data['name'],
                        'child' => $this->companySource->getElementOptionsArray()
                    ]
                ]
            ],
            'attributes' => [
                'class' => \Magento\Ui\Component\Action::class,
                'name' => $data['name']
            ],
            'children' => []
        ];

        return $result;
    }
}

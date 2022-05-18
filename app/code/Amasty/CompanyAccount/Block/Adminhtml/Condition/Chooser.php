<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Block\Adminhtml\Condition;

//Magento doesn't support ui grid
class Chooser extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Amasty\CompanyAccount\Model\ResourceModel\Company\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Amasty\CompanyAccount\Model\Source\Company\Status
     */
    private $status;

    /**
     * @var \Amasty\CompanyAccount\Model\Source\Company\GroupGrid
     */
    private $groupGrid;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Amasty\CompanyAccount\Model\ResourceModel\Company\CollectionFactory $collectionFactory,
        \Amasty\CompanyAccount\Model\Source\Company\Status $status,
        \Amasty\CompanyAccount\Model\Source\Company\GroupGrid $groupGrid,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->collectionFactory = $collectionFactory;
        $this->status = $status;
        $this->groupGrid = $groupGrid;
    }

    protected function _construct()
    {
        parent::_construct();

        if ($this->getRequest()->getParam('current_grid_id')) {
            $this->setId($this->getRequest()->getParam('current_grid_id'));
        } else {
            $this->setId('skuChooserGrid_' . $this->getId());
        }

        $form = $this->getJsFormObject();
        $this->setRowClickCallback("{$form}.chooserGridRowClick.bind({$form})");
        $this->setCheckboxCheckCallback("{$form}.chooserGridCheckboxCheck.bind({$form})");
        $this->setRowInitCallback("{$form}.chooserGridRowInit.bind({$form})");
        $this->setDefaultSort('company_id');
        $this->setUseAjax(true);
        if ($this->getRequest()->getParam('collapse')) {
            $this->setIsCollapsed(true);
        }
    }

    protected function _prepareCollection()
    {
        $this->setCollection($this->collectionFactory->create());

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'selected',
            [
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'selected',
                'values' => $this->getRequest()->getPost('selected', []),
                'align' => 'center',
                'index' => 'company_id',
                'use_index' => true
            ]
        );

        $this->addColumn(
            'company_id',
            ['header' => __('ID'), 'sortable' => true, 'width' => '60px', 'index' => 'company_id']
        );

        $this->addColumn(
            'company_name',
            [
                'header' => __('Company Name'),
                'width' => '60px',
                'index' => 'company_name'
            ]
        );

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'width' => '100px',
                'index' => 'status',
                'type' => 'options',
                'options' => $this->status->toArray()
            ]
        );

        $this->addColumn(
            'customer_group',
            [
                'header' => __('Customer Group'),
                'name' => 'customer_group',
                'width' => '80px',
                'index' => 'customer_group',
                'type' => 'options',
                'options' => $this->groupGrid->getGroupsForCondition()
            ]
        );
        $this->addColumn(
            'company_admin',
            ['header' => __('Company Admin'), 'name' => 'company_admin', 'index' => 'company_admin']
        );

        return parent::_prepareColumns();
    }
}

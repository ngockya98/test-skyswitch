<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Block\Credit;

use Amasty\CompanyAccount\Api\Data\CreditEventInterface;
use Amasty\CompanyAccount\Model\ResourceModel\CreditEvent\Collection as CreditEventCollection;
use Amasty\CompanyAccount\ViewModel\CreditEvent;
use Magento\Framework\View\Element\Template;
use Magento\Theme\Block\Html\Pager;

class Grid extends Template
{
    /**
     * @var null|CreditEventCollection
     */
    private $collection;

    /**
     * @var null|string
     */
    private $pagerHtml;

    public function getCreditEventHelper(): CreditEvent
    {
        return $this->getData('creditEvent');
    }

    public function getCollection(): CreditEventCollection
    {
        if ($this->collection === null) {
            $this->collection = $this->getCreditEventHelper()->getCreditEventsForCompanyContext();
        }

        return $this->collection;
    }

    /**
     * @return CreditEventInterface[]
     */
    public function getCreditEvents(): array
    {
        return $this->getCollection()->getItems();
    }

    /**
     * @return Grid
     */
    protected function _beforeToHtml()
    {
        // trigger load collection with applying pager filters
        $this->getPagerHtml();
        return parent::_beforeToHtml();
    }

    public function getPagerHtml(): string
    {
        if ($this->pagerHtml === null) {
            /** @var Pager $pager */
            $pager = $this->getChildBlock('pager');

            if ($pager) {
                $pager->setCollection($this->getCollection());
                $this->pagerHtml = $pager->toHtml();
            } else {
                $this->pagerHtml = '';
            }
        }

        return $this->pagerHtml;
    }
}

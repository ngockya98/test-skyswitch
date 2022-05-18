<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Condition;

use Magento\Rule\Model\Condition\AbstractCondition;

class Company extends AbstractCondition
{
    public function loadAttributeOptions(): Company
    {
        $this->setAttributeOption(['company_id' => __('Company')]);

        return $this;
    }

    public function loadOperatorOptions(): Company
    {
        $this->setOperatorOption(
            [
                '()' => __('is one of'),
                '!()' => __('is not one of'),
            ]
        );

        return $this;
    }

    public function getValueAfterElementHtml(): string
    {
        $html = '';
        $image = $this->_assetRepo->getUrl('images/rule_chooser_trigger.gif');

        if (!empty($image)) {
            $html = sprintf(
                '<a href="javascript:void(0)" class="rule-chooser-trigger">'
                . '<img src="%s" alt="" class="v-middle rule-chooser-trigger" title="%s" /></a>',
                $image,
                __('Open Chooser')
            );
        }

        return $html;
    }

    public function getValueElementChooserUrl(): string
    {
        return $this->getData('backendData')->getUrl('amcompany/condition/chooser/form/' . $this->getJsFormObject());
    }

    public function getAttributeElement(): \Magento\Framework\Data\Form\Element\Select
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);

        return $element;
    }

    public function getExplicitApply(): bool
    {
        return true;
    }

    public function collectValidatedAttributes($collection): Company
    {
        return $this;
    }

    public function validate(\Magento\Framework\Model\AbstractModel $model): bool
    {
        $customer = $this->getData('customerResource')
            ->getCustomerExtensionAttributes($model->getCustomerId() ?: $model->getEntityId());
        $issetCompanyId = $customer && isset($customer['company_id']) && $customer['company_id'];

        return $issetCompanyId && $this->validateAttribute($customer['company_id']);
    }
}

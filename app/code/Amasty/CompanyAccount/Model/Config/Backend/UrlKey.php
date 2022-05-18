<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Config\Backend;

class UrlKey extends \Magento\Framework\App\Config\Value
{
    /**
     * @return UrlKey
     */
    public function beforeSave()
    {
        if ($this->isValueChanged()) {
            /** @var \Magento\Framework\Escaper $escaper */
            $escaper = $this->getData('escaper');
            $value = str_replace([' ', '/'], '-', strtolower($this->getValue()));
            $this->setValue($escaper->escapeUrl($value));
        }

        return parent::beforeSave();
    }
}

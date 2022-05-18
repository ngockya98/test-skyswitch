<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Ui\Component\Company\Form;

use Amasty\Base\Model\ModuleInfoProvider;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Form\Field;

class RestrictedPayments extends Field
{
    /**
     * @var ModuleInfoProvider
     */
    private $moduleInfoProvider;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ModuleInfoProvider $moduleInfoProvider,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->moduleInfoProvider = $moduleInfoProvider;
    }

    public function prepare()
    {
        parent::prepare();
        $config = $this->getData('config');
        if ($this->moduleInfoProvider->isOriginMarketplace()) {
            unset($config['additionalInfo']);
        }

        $this->setData('config', $config);
    }
}

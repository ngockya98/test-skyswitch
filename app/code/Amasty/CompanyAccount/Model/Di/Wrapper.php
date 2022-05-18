<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Di;

use Magento\Framework\View\Element\Template;

class Wrapper extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManagerInterface;
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $objects = [];

    public function __construct(
        Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManagerInterface,
        $name = '',
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->objectManagerInterface = $objectManagerInterface;
        $this->name = $name;
    }

    public function __call($method, $args)
    {
        $result = false;
        if ($this->name && class_exists($this->name)) {
            if (!isset($this->objects[$this->name])) {
                $this->objects[$this->name] = $this->objectManagerInterface->create($this->name);
            }

            // @codingStandardsIgnoreLine
            $result = call_user_func_array([$this->objects[$this->name], $method], $args);
        }

        return $result;
    }
}

<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Ui\Component\CreditEvent\Listing\Column;

use Amasty\CompanyAccount\Model\Credit\Event\Comment\FormatComments;
use Magento\Framework\Escaper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class EventComment extends Column
{
    /**
     * @var FormatComments
     */
    private $formatComments;

    /**
     * @var Escaper
     */
    private $escaper;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        FormatComments $formatComments,
        Escaper $escaper,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->formatComments = $formatComments;
        $this->escaper = $escaper;
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                if (!empty($item[$fieldName])) {
                    $item[$fieldName] = $this->escaper->escapeHtml(
                        nl2br($this->formatComments->execute($item[$fieldName])),
                        ['br', 'a']
                    );
                }
            }
        }

        return $dataSource;
    }
}

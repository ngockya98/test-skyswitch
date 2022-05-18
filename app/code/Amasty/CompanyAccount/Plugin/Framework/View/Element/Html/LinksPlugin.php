<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Plugin\Framework\View\Element\Html;

use Magento\Framework\View\Element\Html\Links;

class LinksPlugin extends Links
{
    public const ORDER_HISTORY_PATH = 'sales/order/history';

    /**
     * @param Links $subject
     * @param mixed $result
     * @param string $path
     */
    public function afterSetActive(Links $subject, $result, string $path)
    {
        $link = $subject->getLinkByPath($path);
        if ($path == self::ORDER_HISTORY_PATH) {
            $link->setIsHighlighted(false);
        }
    }
}

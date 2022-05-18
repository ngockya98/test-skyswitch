<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Credit\Event;

use Amasty\CompanyAccount\Api\Data\CreditEventInterface;
use Amasty\CompanyAccount\Model\Credit\Event\Comment\FormatComments;

class RetrieveComments
{
    /**
     * @var FormatComments
     */
    private $formatComments;

    public function __construct(FormatComments $formatComments)
    {
        $this->formatComments = $formatComments;
    }

    public function execute(CreditEventInterface $creditEvent): string
    {
        $result = '';
        if ($creditEvent->getComment()) {
            $result = $this->formatComments->execute($creditEvent->getComment());
        }

        return $result;
    }
}

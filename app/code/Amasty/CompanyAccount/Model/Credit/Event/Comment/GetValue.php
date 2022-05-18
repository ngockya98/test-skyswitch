<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Credit\Event\Comment;

use Amasty\CompanyAccount\Api\Data\CreditEventInterface;
use Magento\Framework\Serialize\Serializer\Json;

class GetValue
{
    /**
     * @var Json
     */
    private $jsonSerializer;

    public function __construct(Json $jsonSerializer)
    {
        $this->jsonSerializer = $jsonSerializer;
    }

    public function execute(CreditEventInterface $creditEvent, string $key): ?string
    {
        if ($creditEvent->getComment()) {
            $comments = $this->jsonSerializer->unserialize($creditEvent->getComment());
        }

        return $comments[$key] ?? null;
    }
}

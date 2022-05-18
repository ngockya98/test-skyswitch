<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Credit\Event\Comment;

use Magento\Framework\Serialize\Serializer\Json;

class FormatComments
{
    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var RetrieveStrategyInterface[]
     */
    private $retrievers;

    public function __construct(
        Json $jsonSerializer,
        array $retrievers = []
    ) {
        $this->jsonSerializer = $jsonSerializer;
        $this->retrievers = $retrievers;
    }

    public function execute(string $comment): string
    {
        $result = '';

        $comments = $this->jsonSerializer->unserialize($comment);
        foreach ($comments as $commentType => $comment) {
            if (isset($this->retrievers[$commentType])) {
                $result .= $this->retrievers[$commentType]->execute((string) $comment) . PHP_EOL;
            } else {
                continue;
            }
        }

        return $result;
    }
}

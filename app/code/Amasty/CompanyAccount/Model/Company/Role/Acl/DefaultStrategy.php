<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Company\Role\Acl;

class DefaultStrategy implements IsAclShowedStrategyInterface
{
    public function execute(): bool
    {
        return true;
    }
}

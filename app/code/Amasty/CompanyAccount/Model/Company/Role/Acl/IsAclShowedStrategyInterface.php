<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Company\Role\Acl;

interface IsAclShowedStrategyInterface
{
    public function execute(): bool;
}

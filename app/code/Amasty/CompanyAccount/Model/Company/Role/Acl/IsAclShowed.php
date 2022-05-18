<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Company\Role\Acl;

class IsAclShowed
{
    /**
     * @var IsAclShowedStrategyInterface[]
     */
    private $strategies;

    public function __construct(array $strategies)
    {
        $this->strategies = $strategies;
    }

    /**
     * Based on current user context.
     *
     * @param string $acl
     * @return bool
     */
    public function execute(string $acl): bool
    {
        $strategy = $this->strategies[$acl] ?? $this->strategies['default'];
        return $strategy->execute();
    }
}

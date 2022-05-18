<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Backend\Company;

use Amasty\CompanyAccount\Api\Data\CompanyInterface;

class Registry
{
    /**
     * @var CompanyInterface|null
     */
    private $company;

    public function set(CompanyInterface $company): void
    {
        $this->company = $company;
    }

    public function get(): CompanyInterface
    {
        return $this->company;
    }
}

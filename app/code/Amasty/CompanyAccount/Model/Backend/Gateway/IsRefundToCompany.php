<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Backend\Gateway;

use Magento\Framework\App\RequestInterface;

class IsRefundToCompany
{
    public const REQUEST_PARAM_GROUP = 'creditmemo';
    public const REQUEST_PARAM = 'do_offline_to_company';

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    public function execute(): bool
    {
        $creditMemoData = $this->request->getParam(self::REQUEST_PARAM_GROUP);
        return isset($creditMemoData[self::REQUEST_PARAM]) ? (bool) $creditMemoData[self::REQUEST_PARAM] : true;
    }
}

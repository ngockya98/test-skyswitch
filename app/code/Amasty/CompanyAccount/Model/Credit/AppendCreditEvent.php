<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Credit;

use Amasty\CompanyAccount\Api\CreditRepositoryInterface;
use Amasty\CompanyAccount\Api\Data\CreditEventInterface;
use Amasty\CompanyAccount\Api\Data\CreditInterface;
use Amasty\CompanyAccount\Model\Credit\Event\Action\ChangeCreditStrategyInterface;
use Amasty\CompanyAccount\Model\Credit\Event\UpdateRates;
use Amasty\CompanyAccount\Model\Credit\Event\Validator as EventValidator;
use Amasty\CompanyAccount\Model\ResourceModel\CreditEvent\Append as AppendResource;
use Exception;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Validation\ValidationException;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * @api
 */
class AppendCreditEvent
{
    /**
     * @var CreditRepositoryInterface
     */
    private $creditRepository;

    /**
     * @var EventValidator
     */
    private $eventValidator;

    /**
     * @var AppendResource
     */
    private $appendResource;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ChangeCreditStrategyInterface[]
     */
    private $changeActions;

    /**
     * @var UpdateRates
     */
    private $updateRates;

    public function __construct(
        CreditRepositoryInterface $creditRepository,
        EventValidator $eventValidator,
        AppendResource $appendResource,
        StoreManagerInterface $storeManager,
        UpdateRates $updateRates,
        LoggerInterface $logger,
        array $changeActions = []
    ) {
        $this->creditRepository = $creditRepository;
        $this->eventValidator = $eventValidator;
        $this->appendResource = $appendResource;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->changeActions = $changeActions;
        $this->updateRates = $updateRates;
    }

    /**
     * @param CreditInterface $credit
     * @param CreditEventInterface[] $creditEvents
     * @return void
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws ValidationException
     */
    public function execute(CreditInterface $credit, array $creditEvents): void
    {
        if (empty($creditEvents)) {
            throw new InputException(__('Input data is empty'));
        }

        if ($credit->getId() === null) {
            if ($credit->getCurrencyCode() === null) {
                try {
                    $credit->setCurrencyCode($this->storeManager->getWebsite()->getBaseCurrencyCode());
                } catch (LocalizedException $e) {
                    $this->logger->error($e->getMessage());
                }
            }
            $this->creditRepository->save($credit);
        }

        foreach ($creditEvents as $creditEvent) {
            if ($creditEvent->getId() !== null) {
                $message =  __(
                    'Cannot update Credit event %credit_event',
                    ['credit_event' => $creditEvent->getId()]
                );
                $this->logger->error($message);
                throw new InputException($message);
            }

            $this->eventValidator->execute($credit, $creditEvent);

            $creditEvent->updateAmount();

            $changeAction = $this->changeActions[$creditEvent->getType()] ?? $this->changeActions['default'];
            $changeAction->execute($credit, $creditEvent);

            $creditEvent->setCreditId((int) $credit->getId());

            $this->updateRates->execute($creditEvent);
        }

        try {
            $this->appendResource->execute($credit, $creditEvents);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotSaveException(__('Could not append Credit events'), $e);
        }
    }
}

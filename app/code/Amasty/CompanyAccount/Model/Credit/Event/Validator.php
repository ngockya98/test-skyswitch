<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Credit\Event;

use Amasty\CompanyAccount\Api\Data\CreditEventInterface;
use Amasty\CompanyAccount\Api\Data\CreditInterface;
use Amasty\CompanyAccount\Model\Credit\Event\Condition\ConditionInterface;
use Amasty\CompanyAccount\Model\Source\Credit\Operation;
use Magento\Framework\Validation\ValidationException;
use Magento\Framework\Validation\ValidationResultFactory;

/**
 * @api
 */
class Validator
{
    /**
     * @var array
     */
    private $conditions;

    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    public function __construct(
        ValidationResultFactory $validationResultFactory,
        array $conditions
    ) {
        $this->conditions = $conditions;
        $this->validationResultFactory = $validationResultFactory;
    }

    /**
     * Validate is credit event must be applied for credit.
     *
     * @param CreditInterface $credit
     * @param CreditEventInterface $creditEvent
     * @return void
     * @throws ValidationException
     */
    public function execute(CreditInterface $credit, CreditEventInterface $creditEvent): void
    {
        $conditions = $this->getConditions($creditEvent->getType());
        if (empty($conditions)) {
            return;
        }

        $errors = [];
        foreach ($conditions as $condition) {
            try {
                $condition->validate($credit, $creditEvent);
            } catch (ValidationException $e) {
                $errors[] = __($e->getRawMessage());
            }
        }

        $validationResult = $this->validationResultFactory->create(['errors' => $errors]);
        if (!$validationResult->isValid()) {
            throw new ValidationException(__('Can\'t apply credit event.'), null, 0, $validationResult);
        }
    }

    /**
     * @param string $eventType
     * @return ConditionInterface[]
     */
    private function getConditions(string $eventType): array
    {
        return $this->conditions[$eventType] ?? [];
    }
}

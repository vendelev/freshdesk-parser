<?php

declare(strict_types=1);

namespace Parser\Ticket\Domain\ValueObject;

use InvalidArgumentException;

/**
 * Статус задачи в системе Freshdesk.
 *
 * Представляет статус задачи в системе Freshdesk.
 * Допустимые значения: 2:Open, 3:Pending, 4:Resolved, 5:Closed, 6:Waiting on Customer, 7:Waiting on Third Party
 */
final readonly class TicketStatus
{
    public const int OPEN = 2;

    public const int PENDING = 3;

    public const int RESOLVED = 4;

    public const int CLOSED = 5;

    public const int WAITING_ON_CUSTOMER = 6;

    public const int WAITING_ON_THIRD_PARTY = 7;

    private const array VALID_STATUSES = [
        self::OPEN => 'Open',
        self::PENDING => 'Pending',
        self::RESOLVED => 'Resolved',
        self::CLOSED => 'Closed',
        self::WAITING_ON_CUSTOMER => 'Waiting on Customer',
        self::WAITING_ON_THIRD_PARTY => 'Waiting on Third Party',
    ];

    /**
     * @param int $value Статус задачи
     *
     * @throws InvalidArgumentException Если значение не является допустимым статусом
     */
    public function __construct(
        public int $value
    ) {
        if (!isset(self::VALID_STATUSES[$value])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid ticket status: %d. Valid statuses are: %s',
                    $value,
                    implode(', ', array_keys(self::VALID_STATUSES))
                )
            );
        }
    }

    /**
     * Получить строковое представление статуса задачи.
     *
     * @return string Строковое представление статуса задачи
     */
    public function getLabel(): string
    {
        return self::VALID_STATUSES[$this->value];
    }



    /**
     * Проверить равенство с другим статусом задачи.
     *
     * @param self $other Другой статус задачи
     *
     * @return bool True если статусы равны, иначе false
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Проверить, является ли статус "Open".
     *
     * @return bool True если статус "Open", иначе false
     */
    public function isOpen(): bool
    {
        return $this->value === self::OPEN;
    }

    /**
     * Проверить, является ли статус "Pending".
     *
     * @return bool True если статус "Pending", иначе false
     */
    public function isPending(): bool
    {
        return $this->value === self::PENDING;
    }

    /**
     * Проверить, является ли статус "Resolved".
     *
     * @return bool True если статус "Resolved", иначе false
     */
    public function isResolved(): bool
    {
        return $this->value === self::RESOLVED;
    }

    /**
     * Проверить, является ли статус "Closed".
     *
     * @return bool True если статус "Closed", иначе false
     */
    public function isClosed(): bool
    {
        return $this->value === self::CLOSED;
    }

    /**
     * Проверить, является ли статус "Waiting on Customer".
     *
     * @return bool True если статус "Waiting on Customer", иначе false
     */
    public function isWaitingOnCustomer(): bool
    {
        return $this->value === self::WAITING_ON_CUSTOMER;
    }

    /**
     * Проверить, является ли статус "Waiting on Third Party".
     *
     * @return bool True если статус "Waiting on Third Party", иначе false
     */
    public function isWaitingOnThirdParty(): bool
    {
        return $this->value === self::WAITING_ON_THIRD_PARTY;
    }
}

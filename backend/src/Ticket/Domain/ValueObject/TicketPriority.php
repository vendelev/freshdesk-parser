<?php

declare(strict_types=1);

namespace Parser\Ticket\Domain\ValueObject;

use InvalidArgumentException;

/**
 * Приоритет задачи в системе Freshdesk.
 *
 * Представляет приоритет задачи в системе Freshdesk.
 * Допустимые значения: 1:Low, 2:Medium, 3:High, 4:Urgent
 */
final readonly class TicketPriority
{
    public const int LOW = 1;

    public const int MEDIUM = 2;

    public const int HIGH = 3;

    public const int URGENT = 4;

    private const array VALID_PRIORITIES = [
        self::LOW => 'Low',
        self::MEDIUM => 'Medium',
        self::HIGH => 'High',
        self::URGENT => 'Urgent',
    ];

    /**
     * @param int $value Приоритет задачи
     *
     * @throws InvalidArgumentException Если значение не является допустимым приоритетом
     */
    public function __construct(
        public int $value
    ) {
        if (!isset(self::VALID_PRIORITIES[$value])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid ticket priority: %d. Valid priorities are: %s',
                    $value,
                    implode(', ', array_keys(self::VALID_PRIORITIES))
                )
            );
        }
    }

    /**
     * Получить строковое представление приоритета задачи.
     *
     * @return string Строковое представление приоритета задачи
     */
    public function getLabel(): string
    {
        return self::VALID_PRIORITIES[$this->value];
    }



    /**
     * Проверить равенство с другим приоритетом задачи.
     *
     * @param self $other Другой приоритет задачи
     *
     * @return bool True если приоритеты равны, иначе false
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Проверить, является ли приоритет "Low".
     *
     * @return bool True если приоритет "Low", иначе false
     */
    public function isLow(): bool
    {
        return $this->value === self::LOW;
    }

    /**
     * Проверить, является ли приоритет "Medium".
     *
     * @return bool True если приоритет "Medium", иначе false
     */
    public function isMedium(): bool
    {
        return $this->value === self::MEDIUM;
    }

    /**
     * Проверить, является ли приоритет "High".
     *
     * @return bool True если приоритет "High", иначе false
     */
    public function isHigh(): bool
    {
        return $this->value === self::HIGH;
    }

    /**
     * Проверить, является ли приоритет "Urgent".
     *
     * @return bool True если приоритет "Urgent", иначе false
     */
    public function isUrgent(): bool
    {
        return $this->value === self::URGENT;
    }
}

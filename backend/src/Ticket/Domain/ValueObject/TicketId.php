<?php

declare(strict_types=1);

namespace Parser\Ticket\Domain\ValueObject;

use InvalidArgumentException;

/**
 * Идентификатор задачи в системе Freshdesk.
 *
 * Представляет уникальный идентификатор задачи в системе Freshdesk.
 * Значение должно быть положительным целым числом.
 */
final readonly class TicketId implements \Stringable
{
    /**
     * @param int $value Идентификатор задачи в Freshdesk
     *
     * @throws InvalidArgumentException Если значение не является положительным целым числом
     */
    public function __construct(
        public int $value
    ) {
        if ($value <= 0) {
            throw new InvalidArgumentException('Ticket ID must be a positive integer');
        }
    }


    /**
     * Проверить равенство с другим идентификатором задачи.
     *
     * @param self $other Другой идентификатор задачи
     *
     * @return bool True если идентификаторы равны, иначе false
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Получить строковое представление идентификатора задачи.
     *
     * @return string Строковое представление идентификатора задачи
     */
    public function __toString(): string
    {
        return (string) $this->value;
    }
}

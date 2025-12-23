<?php

declare(strict_types=1);

namespace Parser\Ticket\Domain\ValueObject;

use InvalidArgumentException;

/**
 * Источник задачи в системе Freshdesk.
 *
 * Представляет источник задачи в системе Freshdesk.
 * Допустимые значения: 1:Email, 2:Portal, 3:Phone, 7:Chat, 9:Mobihelp, 10:Feedback Widget, 11:Outbound Email
 */
final readonly class TicketSource
{
    public const int EMAIL = 1;

    public const int PORTAL = 2;

    public const int PHONE = 3;

    public const int CHAT = 7;

    public const int MOBIHELP = 9;

    public const int FEEDBACK_WIDGET = 10;

    public const int OUTBOUND_EMAIL = 11;

    private const array VALID_SOURCES = [
        self::EMAIL => 'Email',
        self::PORTAL => 'Portal',
        self::PHONE => 'Phone',
        self::CHAT => 'Chat',
        self::MOBIHELP => 'Mobihelp',
        self::FEEDBACK_WIDGET => 'Feedback Widget',
        self::OUTBOUND_EMAIL => 'Outbound Email',
    ];

    /**
     * @param int $value Источник задачи
     *
     * @throws InvalidArgumentException Если значение не является допустимым источником
     */
    public function __construct(
        public int $value
    ) {
        if (!isset(self::VALID_SOURCES[$value])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid ticket source: %d. Valid sources are: %s',
                    $value,
                    implode(', ', array_keys(self::VALID_SOURCES))
                )
            );
        }
    }

    /**
     * Получить строковое представление источника задачи.
     *
     * @return string Строковое представление источника задачи
     */
    public function getLabel(): string
    {
        return self::VALID_SOURCES[$this->value];
    }



    /**
     * Проверить равенство с другим источником задачи.
     *
     * @param self $other Другой источник задачи
     *
     * @return bool True если источники равны, иначе false
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Проверить, является ли источник "Email".
     *
     * @return bool True если источник "Email", иначе false
     */
    public function isEmail(): bool
    {
        return $this->value === self::EMAIL;
    }

    /**
     * Проверить, является ли источник "Portal".
     *
     * @return bool True если источник "Portal", иначе false
     */
    public function isPortal(): bool
    {
        return $this->value === self::PORTAL;
    }

    /**
     * Проверить, является ли источник "Phone".
     *
     * @return bool True если источник "Phone", иначе false
     */
    public function isPhone(): bool
    {
        return $this->value === self::PHONE;
    }

    /**
     * Проверить, является ли источник "Chat".
     *
     * @return bool True если источник "Chat", иначе false
     */
    public function isChat(): bool
    {
        return $this->value === self::CHAT;
    }

    /**
     * Проверить, является ли источник "Mobihelp".
     *
     * @return bool True если источник "Mobihelp", иначе false
     */
    public function isMobihelp(): bool
    {
        return $this->value === self::MOBIHELP;
    }

    /**
     * Проверить, является ли источник "Feedback Widget".
     *
     * @return bool True если источник "Feedback Widget", иначе false
     */
    public function isFeedbackWidget(): bool
    {
        return $this->value === self::FEEDBACK_WIDGET;
    }

    /**
     * Проверить, является ли источник "Outbound Email".
     *
     * @return bool True если источник "Outbound Email", иначе false
     */
    public function isOutboundEmail(): bool
    {
        return $this->value === self::OUTBOUND_EMAIL;
    }
}

<?php

declare(strict_types=1);

namespace Tests\Suite\Ticket\Domain;

use InvalidArgumentException;
use Parser\Ticket\Domain\ValueObject\TicketSource;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Тесты для источника задачи.
 *
 */
#[CoversClass(TicketSource::class)]
final class TicketSourceTest extends TestCase
{
    /**
     * Тест создания источника задачи с корректным значением.
     */
    public function testTicketSourceCreationWithValidValue(): void
    {
        $source = new TicketSource(TicketSource::EMAIL);

        self::assertSame(TicketSource::EMAIL, $source->value);
        self::assertSame('Email', $source->getLabel());
        self::assertTrue($source->isEmail());
        self::assertFalse($source->isPortal());
    }

    /**
     * Тест создания источника задачи с некорректным значением.
     */
    public function testTicketSourceCreationWithInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid ticket source: 99. Valid sources are: 1, 2, 3, 7, 9, 10, 11');

        new TicketSource(99);
    }

    /**
     * Тест проверки равенства источников задач.
     */
    public function testTicketSourceEquality(): void
    {
        $source1 = new TicketSource(TicketSource::EMAIL);
        $source2 = new TicketSource(TicketSource::EMAIL);
        $source3 = new TicketSource(TicketSource::PORTAL);

        self::assertTrue($source1->equals($source2));
        self::assertFalse($source1->equals($source3));
    }

    /**
     * Тест всех возможных источников задачи.
     */
    public function testAllValidSources(): void
    {
        $sources = [
            TicketSource::EMAIL => ['label' => 'Email', 'method' => 'isEmail'],
            TicketSource::PORTAL => ['label' => 'Portal', 'method' => 'isPortal'],
            TicketSource::PHONE => ['label' => 'Phone', 'method' => 'isPhone'],
            TicketSource::CHAT => ['label' => 'Chat', 'method' => 'isChat'],
            TicketSource::MOBIHELP => ['label' => 'Mobihelp', 'method' => 'isMobihelp'],
            TicketSource::FEEDBACK_WIDGET => ['label' => 'Feedback Widget', 'method' => 'isFeedbackWidget'],
            TicketSource::OUTBOUND_EMAIL => ['label' => 'Outbound Email', 'method' => 'isOutboundEmail'],
        ];

        foreach ($sources as $value => $data) {
            $source = new TicketSource($value);

            self::assertSame($value, $source->value);
            self::assertSame($data['label'], $source->getLabel());

            // Проверяем соответствующий метод
            $method = $data['method'];
            self::assertTrue($source->$method());

            // Проверяем, что другие методы возвращают false
            $otherMethods = array_filter(array_column($sources, 'method'), fn(string $m): bool => $m !== $method);
            foreach ($otherMethods as $otherMethod) {
                self::assertFalse($source->$otherMethod());
            }
        }
    }
}

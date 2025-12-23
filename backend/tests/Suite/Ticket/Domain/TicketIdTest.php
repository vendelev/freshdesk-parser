<?php

declare(strict_types=1);

namespace Tests\Suite\Ticket\Domain;

use InvalidArgumentException;
use Parser\Ticket\Domain\ValueObject\TicketId;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Тесты для идентификатора задачи.
 *
 */
#[CoversClass(TicketId::class)]
final class TicketIdTest extends TestCase
{
    /**
     * Тест создания идентификатора задачи с корректным значением.
     */
    public function testTicketIdCreationWithValidValue(): void
    {
        $ticketId = new TicketId(12345);

        self::assertSame(12345, $ticketId->value);
        self::assertSame('12345', (string) $ticketId);
    }

    /**
     * Тест создания идентификатора задачи с нулевым значением.
     */
    public function testTicketIdCreationWithZeroValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Ticket ID must be a positive integer');

        new TicketId(0);
    }

    /**
     * Тест создания идентификатора задачи с отрицательным значением.
     */
    public function testTicketIdCreationWithNegativeValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Ticket ID must be a positive integer');

        new TicketId(-123);
    }

    /**
     * Тест проверки равенства идентификаторов задач.
     */
    public function testTicketIdEquality(): void
    {
        $ticketId1 = new TicketId(12345);
        $ticketId2 = new TicketId(12345);
        $ticketId3 = new TicketId(54321);

        self::assertTrue($ticketId1->equals($ticketId2));
        self::assertFalse($ticketId1->equals($ticketId3));
    }
}

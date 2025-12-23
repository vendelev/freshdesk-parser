<?php

declare(strict_types=1);

namespace Tests\Suite\Ticket\Domain;

use InvalidArgumentException;
use Parser\Ticket\Domain\ValueObject\TicketStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Тесты для статуса задачи.
 *
 */
#[CoversClass(TicketStatus::class)]
final class TicketStatusTest extends TestCase
{
    /**
     * Тест создания статуса задачи с корректным значением.
     */
    public function testTicketStatusCreationWithValidValue(): void
    {
        $status = new TicketStatus(TicketStatus::OPEN);

        self::assertSame(TicketStatus::OPEN, $status->value);
        self::assertSame('Open', $status->getLabel());
        self::assertTrue($status->isOpen());
        self::assertFalse($status->isPending());
    }

    /**
     * Тест создания статуса задачи с некорректным значением.
     */
    public function testTicketStatusCreationWithInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Invalid ticket status: 99. Valid statuses are: 2, 3, 4, 5, 6, 7'
        );

        new TicketStatus(99);
    }

    /**
     * Тест проверки равенства статусов задач.
     */
    public function testTicketStatusEquality(): void
    {
        $status1 = new TicketStatus(TicketStatus::OPEN);
        $status2 = new TicketStatus(TicketStatus::OPEN);
        $status3 = new TicketStatus(TicketStatus::PENDING);

        self::assertTrue($status1->equals($status2));
        self::assertFalse($status1->equals($status3));
    }

    /**
     * Тест всех возможных статусов задачи.
     */
    public function testAllValidStatuses(): void
    {
        $statuses = [
            TicketStatus::OPEN => ['label' => 'Open', 'method' => 'isOpen'],
            TicketStatus::PENDING => ['label' => 'Pending', 'method' => 'isPending'],
            TicketStatus::RESOLVED => ['label' => 'Resolved', 'method' => 'isResolved'],
            TicketStatus::CLOSED => ['label' => 'Closed', 'method' => 'isClosed'],
            TicketStatus::WAITING_ON_CUSTOMER => ['label' => 'Waiting on Customer', 'method' => 'isWaitingOnCustomer'],
            TicketStatus::WAITING_ON_THIRD_PARTY => [
                'label' => 'Waiting on Third Party',
                'method' => 'isWaitingOnThirdParty'
            ],
        ];

        foreach ($statuses as $value => $data) {
            $status = new TicketStatus($value);

            self::assertSame($value, $status->value);
            self::assertSame($data['label'], $status->getLabel());

            // Проверяем соответствующий метод
            $method = $data['method'];
            self::assertTrue($status->$method());

            // Проверяем, что другие методы возвращают false
            $otherMethods = array_filter(array_column($statuses, 'method'), fn(string $m): bool => $m !== $method);
            foreach ($otherMethods as $otherMethod) {
                self::assertFalse($status->$otherMethod());
            }
        }
    }
}

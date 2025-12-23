<?php

declare(strict_types=1);

namespace Tests\Suite\Ticket\Domain;

use InvalidArgumentException;
use Parser\Ticket\Domain\ValueObject\TicketPriority;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Тесты для приоритета задачи.
 *
 */
#[CoversClass(TicketPriority::class)]
final class TicketPriorityTest extends TestCase
{
    /**
     * Тест создания приоритета задачи с корректным значением.
     */
    public function testTicketPriorityCreationWithValidValue(): void
    {
        $priority = new TicketPriority(TicketPriority::HIGH);

        self::assertSame(TicketPriority::HIGH, $priority->value);
        self::assertSame('High', $priority->getLabel());
        self::assertTrue($priority->isHigh());
        self::assertFalse($priority->isLow());
    }

    /**
     * Тест создания приоритета задачи с некорректным значением.
     */
    public function testTicketPriorityCreationWithInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid ticket priority: 99. Valid priorities are: 1, 2, 3, 4');

        new TicketPriority(99);
    }

    /**
     * Тест проверки равенства приоритетов задач.
     */
    public function testTicketPriorityEquality(): void
    {
        $priority1 = new TicketPriority(TicketPriority::HIGH);
        $priority2 = new TicketPriority(TicketPriority::HIGH);
        $priority3 = new TicketPriority(TicketPriority::LOW);

        self::assertTrue($priority1->equals($priority2));
        self::assertFalse($priority1->equals($priority3));
    }

    /**
     * Тест всех возможных приоритетов задачи.
     */
    public function testAllValidPriorities(): void
    {
        $priorities = [
            TicketPriority::LOW => ['label' => 'Low', 'method' => 'isLow'],
            TicketPriority::MEDIUM => ['label' => 'Medium', 'method' => 'isMedium'],
            TicketPriority::HIGH => ['label' => 'High', 'method' => 'isHigh'],
            TicketPriority::URGENT => ['label' => 'Urgent', 'method' => 'isUrgent'],
        ];

        foreach ($priorities as $value => $data) {
            $priority = new TicketPriority($value);

            self::assertSame($value, $priority->value);
            self::assertSame($data['label'], $priority->getLabel());

            // Проверяем соответствующий метод
            $method = $data['method'];
            self::assertTrue($priority->$method());

            // Проверяем, что другие методы возвращают false
            $otherMethods = array_filter(array_column($priorities, 'method'), fn(string $m): bool => $m !== $method);
            foreach ($otherMethods as $otherMethod) {
                self::assertFalse($priority->$otherMethod());
            }
        }
    }
}

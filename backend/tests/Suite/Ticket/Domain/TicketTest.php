<?php

declare(strict_types=1);

namespace Tests\Suite\Ticket\Domain;

use DateTimeImmutable;
use Parser\Ticket\Domain\Entity\Ticket;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Тесты для сущности Ticket.
 *
 */
#[CoversClass(Ticket::class)]
final class TicketTest extends TestCase
{
    /**
     * Тест создания сущности Ticket и получения значений.
     */
    public function testTicketCreationAndGetters(): void
    {
        $createdAt = new DateTimeImmutable('2023-01-01 10:00:00');
        $updatedAt = new DateTimeImmutable('2023-01-02 15:30:00');
        $dueBy = new DateTimeImmutable('2023-01-10 12:00:00');
        $frDueBy = new DateTimeImmutable('2023-01-01 11:00:00');
        $syncedAt = new DateTimeImmutable('2023-01-02 16:00:00');

        $ticket = new Ticket(
            freshdeskId: 12345,
            subject: 'Test Subject',
            description: '<p>Test Description</p>',
            descriptionText: 'Test Description',
            type: 'ticket',
            status: 2,
            priority: 3,
            source: 1,
            requesterId: 1001,
            responderId: 2001,
            companyId: 3001,
            groupId: 4001,
            productId: 5001,
            email: 'test@example.com',
            name: 'Test User',
            phone: '+1234567890',
            facebookId: 'fb123',
            twitterId: 'tw123',
            ccEmails: ['cc1@example.com', 'cc2@example.com'],
            toEmails: ['to1@example.com', 'to2@example.com'],
            fwdEmails: ['fwd1@example.com', 'fwd2@example.com'],
            replyCcEmails: ['reply1@example.com', 'reply2@example.com'],
            emailConfigId: 6001,
            isEscalated: true,
            spam: false,
            deleted: false,
            customFields: ['field1' => 'value1', 'field2' => 'value2'],
            tags: ['tag1', 'tag2'],
            attachments: [['id' => 1, 'name' => 'file1.txt']],
            dueBy: $dueBy,
            frDueBy: $frDueBy,
            isOverdue: false,
            frEscalated: true,
            slaPolicyId: 7001,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            syncedAt: $syncedAt
        );

        // Проверяем публичные свойства
        self::assertSame(12345, $ticket->freshdeskId);
        self::assertSame('Test Subject', $ticket->subject);
        self::assertSame('<p>Test Description</p>', $ticket->description);
        self::assertSame('Test Description', $ticket->descriptionText);
        self::assertSame('ticket', $ticket->type);
        self::assertSame(2, $ticket->status);
        self::assertSame(3, $ticket->priority);
        self::assertSame(1, $ticket->source);
        self::assertSame(1001, $ticket->requesterId);
        self::assertSame(2001, $ticket->responderId);
        self::assertSame(3001, $ticket->companyId);
        self::assertSame(4001, $ticket->groupId);
        self::assertSame(5001, $ticket->productId);
        self::assertSame('test@example.com', $ticket->email);
        self::assertSame('Test User', $ticket->name);
        self::assertSame('+1234567890', $ticket->phone);
        self::assertSame('fb123', $ticket->facebookId);
        self::assertSame('tw123', $ticket->twitterId);
        self::assertSame(['cc1@example.com', 'cc2@example.com'], $ticket->ccEmails);
        self::assertSame(['to1@example.com', 'to2@example.com'], $ticket->toEmails);
        self::assertSame(['fwd1@example.com', 'fwd2@example.com'], $ticket->fwdEmails);
        self::assertSame(['reply1@example.com', 'reply2@example.com'], $ticket->replyCcEmails);
        self::assertSame(6001, $ticket->emailConfigId);
        self::assertTrue($ticket->isEscalated);
        self::assertFalse($ticket->spam);
        self::assertFalse($ticket->deleted);
        self::assertSame(['field1' => 'value1', 'field2' => 'value2'], $ticket->customFields);
        self::assertSame(['tag1', 'tag2'], $ticket->tags);
        self::assertSame([['id' => 1, 'name' => 'file1.txt']], $ticket->attachments);
        self::assertSame($dueBy, $ticket->dueBy);
        self::assertSame($frDueBy, $ticket->frDueBy);
        self::assertFalse($ticket->isOverdue);
        self::assertTrue($ticket->frEscalated);
        self::assertSame(7001, $ticket->slaPolicyId);
        self::assertSame($createdAt, $ticket->createdAt);
        self::assertSame($updatedAt, $ticket->updatedAt);
        self::assertSame($syncedAt, $ticket->syncedAt);
    }

    /**
     * Тест создания сущности Ticket с null значениями.
     */
    public function testTicketCreationWithNullValues(): void
    {
        $createdAt = new DateTimeImmutable('2023-01-01 10:00:00');
        $updatedAt = new DateTimeImmutable('2023-01-02 15:30:00');

        $ticket = new Ticket(
            freshdeskId: 12345,
            subject: null,
            description: null,
            descriptionText: null,
            type: null,
            status: null,
            priority: null,
            source: null,
            requesterId: null,
            responderId: null,
            companyId: null,
            groupId: null,
            productId: null,
            email: null,
            name: null,
            phone: null,
            facebookId: null,
            twitterId: null,
            ccEmails: null,
            toEmails: null,
            fwdEmails: null,
            replyCcEmails: null,
            emailConfigId: null,
            isEscalated: false,
            spam: false,
            deleted: false,
            customFields: null,
            tags: null,
            attachments: null,
            dueBy: null,
            frDueBy: null,
            isOverdue: false,
            frEscalated: null,
            slaPolicyId: null,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            syncedAt: null
        );

        // Проверяем публичные свойства с null значениями
        self::assertNull($ticket->subject);
        self::assertNull($ticket->description);
        self::assertNull($ticket->descriptionText);
        self::assertNull($ticket->type);
        self::assertNull($ticket->status);
        self::assertNull($ticket->priority);
        self::assertNull($ticket->source);
        self::assertNull($ticket->requesterId);
        self::assertNull($ticket->responderId);
        self::assertNull($ticket->companyId);
        self::assertNull($ticket->groupId);
        self::assertNull($ticket->productId);
        self::assertNull($ticket->email);
        self::assertNull($ticket->name);
        self::assertNull($ticket->phone);
        self::assertNull($ticket->facebookId);
        self::assertNull($ticket->twitterId);
        self::assertNull($ticket->ccEmails);
        self::assertNull($ticket->toEmails);
        self::assertNull($ticket->fwdEmails);
        self::assertNull($ticket->replyCcEmails);
        self::assertNull($ticket->emailConfigId);
        self::assertFalse($ticket->isEscalated);
        self::assertFalse($ticket->spam);
        self::assertFalse($ticket->deleted);
        self::assertNull($ticket->customFields);
        self::assertNull($ticket->tags);
        self::assertNull($ticket->attachments);
        self::assertNull($ticket->dueBy);
        self::assertNull($ticket->frDueBy);
        self::assertFalse($ticket->isOverdue);
        self::assertNull($ticket->frEscalated);
        self::assertNull($ticket->slaPolicyId);
        self::assertSame($createdAt, $ticket->createdAt);
        self::assertSame($updatedAt, $ticket->updatedAt);
        self::assertNull($ticket->syncedAt);
    }
}

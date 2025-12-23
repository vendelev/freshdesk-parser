<?php

declare(strict_types=1);

namespace Tests\Suite\Ticket\Application\Service;

use DateTimeImmutable;
use Parser\Ticket\Application\Dto\TicketApiDto;
use Parser\Ticket\Application\Service\TicketTransformer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Тесты для сервиса трансформации API данных в доменную сущность задачи.
 */
#[CoversClass(TicketTransformer::class)]
final class TicketTransformerTest extends TestCase
{
    private TicketTransformer $transformer;

    protected function setUp(): void
    {
        $this->transformer = new TicketTransformer();
    }

    /**
     * Тест трансформации DTO в доменную сущность.
     *
     * @throws \DateMalformedStringException Если формат даты некорректен
     */
    public function testTransformFromApi(): void
    {
        $dto = new TicketApiDto(
            id: 123,
            subject: 'Test Subject',
            description: '<p>Test Description</p>',
            descriptionText: 'Test Description',
            type: 'ticket',
            status: 2,
            priority: 3,
            source: 1,
            requesterId: 456,
            responderId: 789,
            companyId: 101,
            groupId: 111,
            productId: 222,
            email: 'test@example.com',
            name: 'Test Name',
            phone: '+1234567890',
            facebookId: 'fb123',
            twitterId: 'tw123',
            ccEmails: ['cc1@example.com', 'cc2@example.com'],
            toEmails: ['to1@example.com', 'to2@example.com'],
            fwdEmails: ['fwd1@example.com', 'fwd2@example.com'],
            replyCcEmails: ['reply1@example.com', 'reply2@example.com'],
            emailConfigId: 333,
            isEscalated: true,
            spam: false,
            deleted: false,
            customFields: ['field1' => 'value1', 'field2' => 'value2'],
            tags: ['tag1', 'tag2'],
            attachments: [
                ['id' => 1, 'name' => 'file1.txt'],
                ['id' => 2, 'name' => 'file2.pdf'],
            ],
            dueBy: '2023-12-31T23:59:59Z',
            frDueBy: '2023-12-25T12:00:00Z',
            isOverdue: false,
            frEscalated: true,
            slaPolicyId: 444,
            createdAt: '2023-01-01T00:00:00Z',
            updatedAt: '2023-01-02T00:00:00Z',
        );

        $ticket = $this->transformer->transformFromApi($dto);

        self::assertSame(123, $ticket->freshdeskId);
        self::assertSame('Test Subject', $ticket->subject);
        self::assertSame('<p>Test Description</p>', $ticket->description);
        self::assertSame('Test Description', $ticket->descriptionText);
        self::assertSame('ticket', $ticket->type);
        self::assertSame(2, $ticket->status);
        self::assertSame(3, $ticket->priority);
        self::assertSame(1, $ticket->source);
        self::assertSame(456, $ticket->requesterId);
        self::assertSame(789, $ticket->responderId);
        self::assertSame(101, $ticket->companyId);
        self::assertSame(111, $ticket->groupId);
        self::assertSame(222, $ticket->productId);
        self::assertSame('test@example.com', $ticket->email);
        self::assertSame('Test Name', $ticket->name);
        self::assertSame('+1234567890', $ticket->phone);
        self::assertSame('fb123', $ticket->facebookId);
        self::assertSame('tw123', $ticket->twitterId);
        self::assertSame(['cc1@example.com', 'cc2@example.com'], $ticket->ccEmails);
        self::assertSame(['to1@example.com', 'to2@example.com'], $ticket->toEmails);
        self::assertSame(['fwd1@example.com', 'fwd2@example.com'], $ticket->fwdEmails);
        self::assertSame(['reply1@example.com', 'reply2@example.com'], $ticket->replyCcEmails);
        self::assertSame(333, $ticket->emailConfigId);
        self::assertTrue($ticket->isEscalated);
        self::assertFalse($ticket->spam);
        self::assertFalse($ticket->deleted);
        self::assertSame(['field1' => 'value1', 'field2' => 'value2'], $ticket->customFields);
        self::assertSame(['tag1', 'tag2'], $ticket->tags);
        self::assertSame(
            [
                ['id' => 1, 'name' => 'file1.txt'],
                ['id' => 2, 'name' => 'file2.pdf'],
            ],
            $ticket->attachments
        );
        self::assertEquals(new DateTimeImmutable('2023-12-31T23:59:59Z'), $ticket->dueBy);
        self::assertEquals(new DateTimeImmutable('2023-12-25T12:00:00Z'), $ticket->frDueBy);
        self::assertFalse($ticket->isOverdue);
        self::assertTrue($ticket->frEscalated);
        self::assertSame(444, $ticket->slaPolicyId);
        self::assertEquals(new DateTimeImmutable('2023-01-01T00:00:00Z'), $ticket->createdAt);
        self::assertEquals(new DateTimeImmutable('2023-01-02T00:00:00Z'), $ticket->updatedAt);
        self::assertInstanceOf(DateTimeImmutable::class, $ticket->syncedAt);
    }

    /**
     * Тест трансформации DTO с минимальными данными.
     *
     * @throws \DateMalformedStringException Если формат даты некорректен
     */
    public function testTransformFromApiWithMinimalData(): void
    {
        $dto = new TicketApiDto(
            id: 123,
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
            createdAt: '2023-01-01T00:00:00Z',
            updatedAt: '2023-01-02T00:00:00Z',
        );

        $ticket = $this->transformer->transformFromApi($dto);

        self::assertSame(123, $ticket->freshdeskId);
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
        self::assertEquals(new DateTimeImmutable('2023-01-01T00:00:00Z'), $ticket->createdAt);
        self::assertEquals(new DateTimeImmutable('2023-01-02T00:00:00Z'), $ticket->updatedAt);
        self::assertInstanceOf(DateTimeImmutable::class, $ticket->syncedAt);
    }
}

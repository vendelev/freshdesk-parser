<?php

declare(strict_types=1);

namespace Tests\Suite\Ticket\Application\Dto;

use Parser\Ticket\Application\Dto\TicketApiDto;
use PHPUnit\Framework\TestCase;

/**
 * Тесты для DTO TicketApiDto.
 */
final class TicketApiDtoTest extends TestCase
{
    /**
     * Тест создания DTO из массива данных.
     */
    public function testFromArray(): void
    {
        $data = [
            'id' => 123,
            'subject' => 'Test Subject',
            'description' => '<p>Test Description</p>',
            'description_text' => 'Test Description',
            'type' => 'ticket',
            'status' => 2,
            'priority' => 3,
            'source' => 1,
            'requester_id' => 456,
            'responder_id' => 789,
            'company_id' => 101,
            'group_id' => 111,
            'product_id' => 222,
            'email' => 'test@example.com',
            'name' => 'Test Name',
            'phone' => '+1234567890',
            'facebook_id' => 'fb123',
            'twitter_id' => 'tw123',
            'cc_emails' => ['cc1@example.com', 'cc2@example.com'],
            'to_emails' => ['to1@example.com', 'to2@example.com'],
            'fwd_emails' => ['fwd1@example.com', 'fwd2@example.com'],
            'reply_cc_emails' => ['reply1@example.com', 'reply2@example.com'],
            'email_config_id' => 333,
            'is_escalated' => true,
            'spam' => false,
            'deleted' => false,
            'custom_fields' => ['field1' => 'value1', 'field2' => 'value2'],
            'tags' => ['tag1', 'tag2'],
            'attachments' => [
                ['id' => 1, 'name' => 'file1.txt'],
                ['id' => 2, 'name' => 'file2.pdf'],
            ],
            'due_by' => '2023-12-31T23:59:59Z',
            'fr_due_by' => '2023-12-25T12:00:00Z',
            'is_overdue' => false,
            'fr_escalated' => true,
            'sla_policy_id' => 444,
            'created_at' => '2023-01-01T00:00:00Z',
            'updated_at' => '2023-01-02T00:00:00Z',
        ];

        $dto = TicketApiDto::fromArray($data);

        self::assertSame(123, $dto->id);
        self::assertSame('Test Subject', $dto->subject);
        self::assertSame('<p>Test Description</p>', $dto->description);
        self::assertSame('Test Description', $dto->descriptionText);
        self::assertSame('ticket', $dto->type);
        self::assertSame(2, $dto->status);
        self::assertSame(3, $dto->priority);
        self::assertSame(1, $dto->source);
        self::assertSame(456, $dto->requesterId);
        self::assertSame(789, $dto->responderId);
        self::assertSame(101, $dto->companyId);
        self::assertSame(111, $dto->groupId);
        self::assertSame(222, $dto->productId);
        self::assertSame('test@example.com', $dto->email);
        self::assertSame('Test Name', $dto->name);
        self::assertSame('+1234567890', $dto->phone);
        self::assertSame('fb123', $dto->facebookId);
        self::assertSame('tw123', $dto->twitterId);
        self::assertSame(['cc1@example.com', 'cc2@example.com'], $dto->ccEmails);
        self::assertSame(['to1@example.com', 'to2@example.com'], $dto->toEmails);
        self::assertSame(['fwd1@example.com', 'fwd2@example.com'], $dto->fwdEmails);
        self::assertSame(['reply1@example.com', 'reply2@example.com'], $dto->replyCcEmails);
        self::assertSame(333, $dto->emailConfigId);
        self::assertTrue($dto->isEscalated);
        self::assertFalse($dto->spam);
        self::assertFalse($dto->deleted);
        self::assertSame(['field1' => 'value1', 'field2' => 'value2'], $dto->customFields);
        self::assertSame(['tag1', 'tag2'], $dto->tags);
        self::assertSame(
            [
                ['id' => 1, 'name' => 'file1.txt'],
                ['id' => 2, 'name' => 'file2.pdf'],
            ],
            $dto->attachments
        );
        self::assertSame('2023-12-31T23:59:59Z', $dto->dueBy);
        self::assertSame('2023-12-25T12:00:00Z', $dto->frDueBy);
        self::assertFalse($dto->isOverdue);
        self::assertTrue($dto->frEscalated);
        self::assertSame(444, $dto->slaPolicyId);
        self::assertSame('2023-01-01T00:00:00Z', $dto->createdAt);
        self::assertSame('2023-01-02T00:00:00Z', $dto->updatedAt);
    }

    /**
     * Тест создания DTO из массива с минимальными данными.
     */
    public function testFromArrayWithMinimalData(): void
    {
        $data = [
            'id' => 123,
            'created_at' => '2023-01-01T00:00:00Z',
            'updated_at' => '2023-01-02T00:00:00Z',
        ];

        $dto = TicketApiDto::fromArray($data);

        self::assertSame(123, $dto->id);
        self::assertNull($dto->subject);
        self::assertNull($dto->description);
        self::assertNull($dto->descriptionText);
        self::assertNull($dto->type);
        self::assertNull($dto->status);
        self::assertNull($dto->priority);
        self::assertNull($dto->source);
        self::assertNull($dto->requesterId);
        self::assertNull($dto->responderId);
        self::assertNull($dto->companyId);
        self::assertNull($dto->groupId);
        self::assertNull($dto->productId);
        self::assertNull($dto->email);
        self::assertNull($dto->name);
        self::assertNull($dto->phone);
        self::assertNull($dto->facebookId);
        self::assertNull($dto->twitterId);
        self::assertNull($dto->ccEmails);
        self::assertNull($dto->toEmails);
        self::assertNull($dto->fwdEmails);
        self::assertNull($dto->replyCcEmails);
        self::assertNull($dto->emailConfigId);
        self::assertFalse($dto->isEscalated);
        self::assertFalse($dto->spam);
        self::assertFalse($dto->deleted);
        self::assertNull($dto->customFields);
        self::assertNull($dto->tags);
        self::assertNull($dto->attachments);
        self::assertNull($dto->dueBy);
        self::assertNull($dto->frDueBy);
        self::assertFalse($dto->isOverdue);
        self::assertNull($dto->frEscalated);
        self::assertNull($dto->slaPolicyId);
        self::assertSame('2023-01-01T00:00:00Z', $dto->createdAt);
        self::assertSame('2023-01-02T00:00:00Z', $dto->updatedAt);
    }
}

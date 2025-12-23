<?php

declare(strict_types=1);

namespace Tests\Suite\Ticket\Application\Dto;

use Parser\Ticket\Application\Dto\TicketListApiDto;
use PHPUnit\Framework\TestCase;

/**
 * Тесты для DTO TicketListApiDto.
 */
final class TicketListApiDtoTest extends TestCase
{
    /**
     * Тест создания DTO из массива данных.
     */
    public function testFromArray(): void
    {
        $ticketData1 = [
            'id' => 123,
            'subject' => 'Test Subject 1',
            'description' => '<p>Test Description 1</p>',
            'description_text' => 'Test Description 1',
            'type' => 'ticket',
            'status' => 2,
            'priority' => 3,
            'source' => 1,
            'requester_id' => 456,
            'responder_id' => 789,
            'company_id' => 101,
            'group_id' => 111,
            'product_id' => 222,
            'email' => 'test1@example.com',
            'name' => 'Test Name 1',
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

        $ticketData2 = [
            'id' => 124,
            'subject' => 'Test Subject 2',
            'description' => '<p>Test Description 2</p>',
            'description_text' => 'Test Description 2',
            'type' => 'ticket',
            'status' => 3,
            'priority' => 2,
            'source' => 2,
            'requester_id' => 457,
            'responder_id' => 790,
            'company_id' => 102,
            'group_id' => 112,
            'product_id' => 223,
            'email' => 'test2@example.com',
            'name' => 'Test Name 2',
            'phone' => '+1234567891',
            'facebook_id' => 'fb124',
            'twitter_id' => 'tw124',
            'cc_emails' => ['cc3@example.com', 'cc4@example.com'],
            'to_emails' => ['to3@example.com', 'to4@example.com'],
            'fwd_emails' => ['fwd3@example.com', 'fwd4@example.com'],
            'reply_cc_emails' => ['reply3@example.com', 'reply4@example.com'],
            'email_config_id' => 334,
            'is_escalated' => false,
            'spam' => false,
            'deleted' => false,
            'custom_fields' => ['field3' => 'value3', 'field4' => 'value4'],
            'tags' => ['tag3', 'tag4'],
            'attachments' => [
                ['id' => 3, 'name' => 'file3.txt'],
                ['id' => 4, 'name' => 'file4.pdf'],
            ],
            'due_by' => '2023-12-30T23:59:59Z',
            'fr_due_by' => '2023-12-24T12:00:00Z',
            'is_overdue' => true,
            'fr_escalated' => false,
            'sla_policy_id' => 445,
            'created_at' => '2023-01-03T00:00:00Z',
            'updated_at' => '2023-01-04T00:00:00Z',
        ];

        $data = [$ticketData1, $ticketData2];
        $page = 1;
        $perPage = 100;
        $totalPages = 5;

        $dto = TicketListApiDto::fromArray($data, $page, $perPage, $totalPages);

        self::assertCount(2, $dto->tickets);
        self::assertSame(123, $dto->tickets[0]->id);
        self::assertSame(124, $dto->tickets[1]->id);
        self::assertSame($page, $dto->page);
        self::assertSame($perPage, $dto->perPage);
        self::assertSame($totalPages, $dto->totalPages);
    }

    /**
     * Тест создания DTO из пустого массива данных.
     */
    public function testFromArrayWithEmptyData(): void
    {
        $data = [];
        $page = 1;
        $perPage = 100;
        $totalPages = 0;

        $dto = TicketListApiDto::fromArray($data, $page, $perPage, $totalPages);

        self::assertCount(0, $dto->tickets);
        self::assertSame($page, $dto->page);
        self::assertSame($perPage, $dto->perPage);
        self::assertSame($totalPages, $dto->totalPages);
    }
}

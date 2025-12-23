<?php

declare(strict_types=1);

namespace Tests\Suite\Ticket\Application\Query;

use Parser\Ticket\Application\Dto\TicketListApiDto;
use Parser\Ticket\Application\Query\GetTicketListQuery;
use Parser\Ticket\Application\Query\GetTicketListQueryHandler;
use Parser\Ticket\Domain\FreshdeskTicketAdapterInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Тесты для handler'а запроса на получение списка задач.
 */
#[CoversClass(GetTicketListQueryHandler::class)]
final class GetTicketListQueryHandlerTest extends TestCase
{
    private GetTicketListQueryHandler $handler;

    private FreshdeskTicketAdapterInterface&MockObject $adapter;

    protected function setUp(): void
    {
        $this->adapter = $this->createMock(FreshdeskTicketAdapterInterface::class);
        $this->handler = new GetTicketListQueryHandler($this->adapter);
    }

    /**
     * Тест обработки запроса с результатом.
     */
    public function testInvokeWithResults(): void
    {
        $ticketData = [
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
            'cc_emails' => ['cc1@example.com'],
            'to_emails' => ['to1@example.com'],
            'fwd_emails' => ['fwd1@example.com'],
            'reply_cc_emails' => ['reply1@example.com'],
            'email_config_id' => 333,
            'is_escalated' => true,
            'spam' => false,
            'deleted' => false,
            'custom_fields' => ['field1' => 'value1'],
            'tags' => ['tag1'],
            'attachments' => [['id' => 1, 'name' => 'file1.txt']],
            'due_by' => '2023-12-31T23:59:59Z',
            'fr_due_by' => '2023-12-25T12:00:00Z',
            'is_overdue' => false,
            'fr_escalated' => true,
            'sla_policy_id' => 444,
            'created_at' => '2023-01-01T00:00:00Z',
            'updated_at' => '2023-01-02T00:00:00Z',
        ];

        $expectedDto = TicketListApiDto::fromArray([$ticketData], 1, 100, 5);

        $this->adapter
            ->expects(self::once())
            ->method('getTickets')
            ->with(1)
            ->willReturn($expectedDto);

        $query = new GetTicketListQuery(page: 1);
        $result = ($this->handler)($query);

        self::assertSame($expectedDto, $result);
        self::assertCount(1, $result->tickets);
        self::assertSame(1, $result->page);
        self::assertSame(100, $result->perPage);
        self::assertSame(5, $result->totalPages);
    }

    /**
     * Тест обработки запроса без результатов (пустой список).
     */
    public function testInvokeWithEmptyResults(): void
    {
        $expectedDto = TicketListApiDto::fromArray([], 2, 100, 0);

        $this->adapter
            ->expects(self::once())
            ->method('getTickets')
            ->with(2)
            ->willReturn($expectedDto);

        $query = new GetTicketListQuery(page: 2);
        $result = ($this->handler)($query);

        self::assertSame($expectedDto, $result);
        self::assertCount(0, $result->tickets);
        self::assertSame(2, $result->page);
        self::assertSame(100, $result->perPage);
        self::assertSame(0, $result->totalPages);
    }

    /**
     * Тест обработки запроса с несколькими задачами.
     */
    public function testInvokeWithMultipleTickets(): void
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
            'facebook_id' => null,
            'twitter_id' => null,
            'cc_emails' => null,
            'to_emails' => null,
            'fwd_emails' => null,
            'reply_cc_emails' => null,
            'email_config_id' => null,
            'is_escalated' => false,
            'spam' => false,
            'deleted' => false,
            'custom_fields' => null,
            'tags' => null,
            'attachments' => null,
            'due_by' => null,
            'fr_due_by' => null,
            'is_overdue' => false,
            'fr_escalated' => null,
            'sla_policy_id' => null,
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
            'facebook_id' => null,
            'twitter_id' => null,
            'cc_emails' => null,
            'to_emails' => null,
            'fwd_emails' => null,
            'reply_cc_emails' => null,
            'email_config_id' => null,
            'is_escalated' => false,
            'spam' => false,
            'deleted' => false,
            'custom_fields' => null,
            'tags' => null,
            'attachments' => null,
            'due_by' => null,
            'fr_due_by' => null,
            'is_overdue' => false,
            'fr_escalated' => null,
            'sla_policy_id' => null,
            'created_at' => '2023-01-03T00:00:00Z',
            'updated_at' => '2023-01-04T00:00:00Z',
        ];

        $expectedDto = TicketListApiDto::fromArray([$ticketData1, $ticketData2], 1, 100, 3);

        $this->adapter
            ->expects(self::once())
            ->method('getTickets')
            ->with(1)
            ->willReturn($expectedDto);

        $query = new GetTicketListQuery(page: 1);
        $result = ($this->handler)($query);

        self::assertCount(2, $result->tickets);
        self::assertSame(123, $result->tickets[0]->id);
        self::assertSame(124, $result->tickets[1]->id);
        self::assertSame(1, $result->page);
        self::assertSame(100, $result->perPage);
        self::assertSame(3, $result->totalPages);
    }

    /**
     * Тест обработки запроса с дефолтной страницей.
     */
    public function testInvokeWithDefaultPage(): void
    {
        $expectedDto = TicketListApiDto::fromArray([], 1, 100, 0);

        $this->adapter
            ->expects(self::once())
            ->method('getTickets')
            ->with(1)
            ->willReturn($expectedDto);

        $query = new GetTicketListQuery();
        $result = ($this->handler)($query);

        self::assertSame($expectedDto, $result);
        self::assertSame(1, $result->page);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Suite\Ticket\Application\Query;

use Parser\Ticket\Application\Dto\TicketApiDto;
use Parser\Ticket\Application\Query\GetTicketQuery;
use Parser\Ticket\Application\Query\GetTicketQueryHandler;
use Parser\Ticket\Domain\FreshdeskTicketAdapterInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Тесты для handler'а запроса на получение данных одной задачи.
 */
#[CoversClass(GetTicketQueryHandler::class)]
final class GetTicketQueryHandlerTest extends TestCase
{
    private GetTicketQueryHandler $handler;

    private FreshdeskTicketAdapterInterface&MockObject $adapter;

    protected function setUp(): void
    {
        $this->adapter = $this->createMock(FreshdeskTicketAdapterInterface::class);
        $this->handler = new GetTicketQueryHandler($this->adapter);
    }

    /**
     * Тест обработки запроса на получение данных одной задачи.
     */
    public function testInvoke(): void
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

        $expectedDto = TicketApiDto::fromArray($ticketData);

        $this->adapter
            ->expects(self::once())
            ->method('getTicket')
            ->with(123)
            ->willReturn($expectedDto);

        $query = new GetTicketQuery(freshdeskId: 123);
        $result = ($this->handler)($query);

        self::assertSame($expectedDto, $result);
        self::assertSame(123, $result->id);
        self::assertSame('Test Subject', $result->subject);
    }

    /**
     * Тест обработки запроса с невалидным ID.
     */
    public function testInvokeWithInvalidId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Freshdesk ID должен быть больше нуля');

        new GetTicketQuery(freshdeskId: 0);
    }
}
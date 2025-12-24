<?php

declare(strict_types=1);

namespace Parser\Ticket\Infrastructure\Adapter;

use Parser\Ticket\Application\Dto\TicketApiDto;
use Parser\Ticket\Application\Dto\TicketListApiDto;
use Parser\Ticket\Domain\FreshdeskTicketAdapterInterface;

/**
 * Адаптер для взаимодействия с Freshdesk API.
 *
 * Реализует anti-corruption layer между Freshdesk API и Application слоем.
 * Преобразует API ответы в доменные DTO объекты.
 */
final class FreshdeskTicketAdapter implements FreshdeskTicketAdapterInterface
{
    /**
     * Получить пагинированный список задач из Freshdesk API.
     *
     * @param int $page Номер страницы (начиная с 1)
     *
     * @return TicketListApiDto Пагинированный список задач
     */
    public function getTickets(int $page = 1): TicketListApiDto
    {
        // Это будет полностью реализовано в Task 20
        // На данный момент возвращаем пустой DTO для тестирования QueryHandler
        return TicketListApiDto::fromArray([], $page, 100, 0);
    }
    
    /**
     * Получить данные одной задачи из Freshdesk API по её ID.
     *
     * @param int $freshdeskId ID задачи в Freshdesk
     *
     * @return TicketApiDto Данные задачи
     */
    public function getTicket(int $freshdeskId): TicketApiDto
    {
        // Это будет полностью реализовано в Task 20
        // На данный момент возвращаем пустой DTO для тестирования QueryHandler
        return TicketApiDto::fromArray([
            'id' => $freshdeskId,
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
        ]);
    }
}

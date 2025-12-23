<?php

declare(strict_types=1);

namespace Parser\Ticket\Domain\Response;

/**
 * DTO для выхода из UseCase с данными одной задачи.
 *
 * Представляет данные задачи для передачи между слоями приложения.
 */
final readonly class TicketResponse
{
    /**
     * @param int $freshdeskId ID задачи в Freshdesk системе
     * @param string|null $subject Тема задачи
     * @param string|null $description HTML версия описания
     * @param string|null $descriptionText Текстовая версия описания (HTML удален)
     * @param string|null $type Тип задачи (ticket, incident, problem, change_request)
     * @param int|null $status Статус задачи (2:Open, 3:Pending, 4:Resolved, 5:Closed,
     *                         6:Waiting on Customer, 7:Waiting on Third Party)
     * @param int|null $priority Приоритет задачи (1:Low, 2:Medium, 3:High, 4:Urgent)
     * @param int|null $source Источник задачи (1:Email, 2:Portal, 3:Phone, 7:Chat,
     *                         9:Mobihelp, 10:Feedback Widget, 11:Outbound Email)
     * @param int|null $requesterId Контакт, создавший задачу
     * @param int|null $responderId Агент, ответивший первым
     * @param int|null $companyId Компания контакта
     * @param int|null $groupId Группа агентов, обслуживающая задачу
     * @param int|null $productId Связанный продукт
     * @param string|null $email Email адрес requester
     * @param string|null $name Имя requester
     * @param string|null $phone Телефон requester
     * @param array<string, mixed>|null $customFields Пользовательские поля
     * @param array<string>|null $tags Массив тегов
     * @param array<array{id: int, name: string}>|null $attachments Список вложений (metadata из API)
     */
    public function __construct(
        public int $freshdeskId,
        public ?string $subject,
        public ?string $description,
        public ?string $descriptionText,
        public ?string $type,
        public ?int $status,
        public ?int $priority,
        public ?int $source,
        public ?int $requesterId,
        public ?int $responderId,
        public ?int $companyId,
        public ?int $groupId,
        public ?int $productId,
        public ?string $email,
        public ?string $name,
        public ?string $phone,
        public ?array $customFields,
        public ?array $tags,
        public ?array $attachments,
    ) {
    }
}

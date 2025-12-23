<?php

declare(strict_types=1);

namespace Parser\Ticket\Domain\Entity;

use DateTimeImmutable;

/**
 * Сущность задачи (ticket) из Freshdesk.
 *
 * Представляет полную структуру задачи, соответствующую Freshdesk API v2.
 *
 * @see https://developers.freshdesk.com/api/#view_a_ticket
 */
final readonly class Ticket
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
     * @param string|null $facebookId Facebook ID requester
     * @param string|null $twitterId Twitter ID requester
     * @param array<string>|null $ccEmails Массив email на копии
     * @param array<string>|null $toEmails Массив email получателей
     * @param array<string>|null $fwdEmails Массив email при пересылке
     * @param array<string>|null $replyCcEmails Массив email для ответа на копию
     * @param int|null $emailConfigId Конфиг входящей почты
     * @param bool $isEscalated Задача эскалирована
     * @param bool $spam Помечено как спам
     * @param bool $deleted Помечено на удаление
     * @param array<string, mixed>|null $customFields Пользовательские поля
     * @param array<string>|null $tags Массив тегов
     * @param array<array{id: int, name: string}>|null $attachments Список вложений (metadata из API)
     * @param DateTimeImmutable|null $dueBy Срок решения задачи
     * @param DateTimeImmutable|null $frDueBy Первый ответ по SLA
     * @param bool $isOverdue Задача просрочена (текущее время > due_by)
     * @param bool|null $frEscalated Эскалация первого ответа
     * @param int|null $slaPolicyId Применяемая SLA политика
     * @param DateTimeImmutable $createdAt Создание задачи в Freshdesk
     * @param DateTimeImmutable $updatedAt Последнее обновление в Freshdesk
     * @param DateTimeImmutable|null $syncedAt Время последней синхронизации в нашу БД
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
        public ?string $facebookId,
        public ?string $twitterId,
        public ?array $ccEmails,
        public ?array $toEmails,
        public ?array $fwdEmails,
        public ?array $replyCcEmails,
        public ?int $emailConfigId,
        public bool $isEscalated,
        public bool $spam,
        public bool $deleted,
        public ?array $customFields,
        public ?array $tags,
        public ?array $attachments,
        public ?DateTimeImmutable $dueBy,
        public ?DateTimeImmutable $frDueBy,
        public bool $isOverdue,
        public ?bool $frEscalated,
        public ?int $slaPolicyId,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt,
        public ?DateTimeImmutable $syncedAt,
    ) {
    }
}

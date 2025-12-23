<?php

declare(strict_types=1);

namespace Parser\Ticket\Domain;

use Parser\Ticket\Domain\Entity\Ticket;

/**
 * Интерфейс репозитория для работы с задачами.
 *
 * Предоставляет методы для сохранения, поиска и удаления задач.
 */
interface TicketRepositoryInterface
{
    /**
     * Сохранить задачу.
     *
     * @param Ticket $ticket Задача для сохранения
     */
    public function save(Ticket $ticket): void;

    /**
     * Найти задачу по идентификатору.
     *
     * @param int $id Идентификатор задачи
     *
     * @return Ticket|null Найденная задача или null, если задача не найдена
     */
    public function findById(int $id): ?Ticket;

    /**
     * Найти задачу по идентификатору в Freshdesk.
     *
     * @param int $freshdeskId Идентификатор задачи в Freshdesk
     *
     * @return Ticket|null Найденная задача или null, если задача не найдена
     */
    public function findByFreshdeskId(int $freshdeskId): ?Ticket;

    /**
     * Удалить задачу по идентификатору.
     *
     * @param int $id Идентификатор задачи
     */
    public function delete(int $id): void;
}

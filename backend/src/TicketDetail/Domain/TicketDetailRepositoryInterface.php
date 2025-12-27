<?php

declare(strict_types=1);

namespace Parser\TicketDetail\Domain;

use Parser\TicketDetail\Domain\ValueObject\TicketDetailMetadata;

interface TicketDetailRepositoryInterface
{
    /**
     * Сохранить детальную информацию о задаче
     *
     * @param int $ticketId ID задачи
     * @param string $jsonData JSON данные задачи
     * @param TicketDetailMetadata $metadata Метаданные сохранения
     * @throws \Parser\TicketDetail\Domain\Exception\TicketDetailSaveException
     */
    public function save(int $ticketId, string $jsonData, TicketDetailMetadata $metadata): void;

    /**
     * Проверить существует ли файл с детальной информацией задачи
     *
     * @param int $ticketId ID задачи
     * @return bool true если файл существует
     */
    public function exists(int $ticketId): bool;
}

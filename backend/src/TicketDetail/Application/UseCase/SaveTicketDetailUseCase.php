<?php

declare(strict_types=1);

namespace Parser\TicketDetail\Application\UseCase;

use Parser\TicketDetail\Domain\Exception\TicketDetailSaveException;
use Parser\TicketDetail\Domain\FreshdeskDetailClientInterface;
use Parser\TicketDetail\Domain\Response\SaveTicketDetailResponse;
use Parser\TicketDetail\Domain\TicketDetailRepositoryInterface;
use Parser\TicketDetail\Domain\ValueObject\TicketDetailMetadata;

final readonly class SaveTicketDetailUseCase
{
    public function __construct(
        private FreshdeskDetailClientInterface $freshdeskClient,
        private TicketDetailRepositoryInterface $repository,
    ) {
    }

    /**
     * Выполнить сохранение детальной информации задачи
     *
     * @param int $ticketId ID задачи
     * @param bool $forceOverwrite Принудительно перезаписать существующий файл
     * @return SaveTicketDetailResponse Результат выполнения
     * @throws TicketDetailSaveException
     */
    public function execute(int $ticketId, bool $forceOverwrite = false): SaveTicketDetailResponse
    {
        // Проверить существует ли уже файл, если не требуется перезапись
        if (!$forceOverwrite && $this->repository->exists($ticketId)) {
            return new SaveTicketDetailResponse(
                $ticketId,
                'success', // считаем как успех, файл уже существует
                sprintf('storage/backups/detail/%d.json', $ticketId),
                new \DateTimeImmutable(),
                'Файл уже существует',
            );
        }

        try {
            // Получить детальную информацию из Freshdesk
            $jsonData = $this->freshdeskClient->getTicketDetail($ticketId);

            // Создать метаданные
            $metadata = new TicketDetailMetadata(
                new \DateTimeImmutable(),
                'success',
                strlen($jsonData),
            );

            // Сохранить данные
            $this->repository->save($ticketId, $jsonData, $metadata);

            return new SaveTicketDetailResponse(
                $ticketId,
                'success',
                sprintf('storage/backups/detail/%d.json', $ticketId),
                $metadata->savedAt,
            );
        } catch (\Throwable $e) {
            // В случае ошибки создать failed response
            return new SaveTicketDetailResponse(
                $ticketId,
                'failed',
                sprintf('storage/backups/detail/%d.json', $ticketId),
                new \DateTimeImmutable(),
                $e->getMessage(),
            );
        }
    }
}

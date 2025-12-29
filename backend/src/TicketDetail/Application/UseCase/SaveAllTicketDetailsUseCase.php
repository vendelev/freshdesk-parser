<?php

declare(strict_types=1);

namespace Parser\TicketDetail\Application\UseCase;

use DateTimeImmutable;
use Parser\TicketDetail\Domain\TicketListProviderInterface;
use Parser\TicketDetail\Domain\ValueObject\ProcessingSummary;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class SaveAllTicketDetailsUseCase
{
    public function __construct(
        private TicketListProviderInterface $ticketListProvider,
        private SaveTicketDetailUseCase $saveTicketDetailUseCase,
        private ?LoggerInterface $logger = null,
    ) {
    }

    /**
     * Выполнить сохранение детальной информации для всех задач
     *
     * @param bool $forceOverwrite Принудительно перезаписать существующие файлы
     * @return ProcessingSummary Сводка обработки
     */
    public function execute(bool $forceOverwrite = false): ProcessingSummary
    {
        $startTime = new DateTimeImmutable();
        $processed = 0;
        $skipped = 0;
        $failed = 0;

        try {
            // Получить список ID задач
            $ticketIds = $this->ticketListProvider->getTicketIds();

            // Подсчитать общее количество задач
            $totalTickets = 0;
            $ticketIdsArray = [];
            foreach ($ticketIds as $ticketId) {
                $ticketIdsArray[] = $ticketId;
                ++$totalTickets;
            }

            if ($this->logger instanceof \Psr\Log\LoggerInterface) {
                $this->logger->info('Начало обработки всех задач', [
                    'total_tickets' => $totalTickets,
                    'force_overwrite' => $forceOverwrite,
                ]);
            }

            // Обработать каждую задачу
            foreach ($ticketIdsArray as $ticketId) {
                try {
                    $response = $this->saveTicketDetailUseCase->execute($ticketId, $forceOverwrite);

                    // Обновить счетчики
                    match ($response->status) {
                        'success' => $processed++,
                        'skipped' => $skipped++,
                        default => $failed++,
                    };

                    if ($this->logger instanceof \Psr\Log\LoggerInterface) {
                        $this->logger->debug('Обработана задача', [
                            'ticket_id' => $ticketId,
                            'status' => $response->status,
                        ]);
                    }
                } catch (Throwable $e) {
                    ++$failed;

                    if ($this->logger instanceof \Psr\Log\LoggerInterface) {
                        $this->logger->error('Ошибка при обработке задачи', [
                            'ticket_id' => $ticketId,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            $endTime = new DateTimeImmutable();

            return new ProcessingSummary(
                $totalTickets,
                $processed,
                $skipped,
                $failed,
                $startTime,
                $endTime,
            );
        } catch (Throwable $e) {
            $endTime = new DateTimeImmutable();

            if ($this->logger instanceof \Psr\Log\LoggerInterface) {
                $this->logger->error('Ошибка при обработке всех задач', [
                    'error' => $e->getMessage(),
                ]);
            }

            // Вернуть сводку с ошибкой
            return new ProcessingSummary(
                0,
                $processed,
                $skipped,
                $failed + 1, // Учитываем ошибку обработки
                $startTime,
                $endTime,
            );
        }
    }
}

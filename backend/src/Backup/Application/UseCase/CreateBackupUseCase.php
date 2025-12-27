<?php

declare(strict_types=1);

namespace Parser\Backup\Application\UseCase;

use DateTimeImmutable;
use Parser\Backup\Domain\BackupProgressInterface;
use Parser\Backup\Domain\BackupStorageInterface;
use Parser\Backup\Domain\Exception\BackupOperationFailedException;
use Parser\Backup\Domain\Exception\FreshdeskApiException;
use Parser\Backup\Domain\FreshdeskClientInterface;
use Parser\Backup\Domain\Response\BackupResponse;

final readonly class CreateBackupUseCase
{
    public function __construct(
        private FreshdeskClientInterface $freshdeskClient,
        private BackupStorageInterface $backupStorage,
    ) {
    }

    public function execute(?BackupProgressInterface $progress = null): BackupResponse
    {
        try {
            $backupId = new DateTimeImmutable()->format('Y-m-d_His');
            $totalTickets = 0;
            $pageNumber = 1;

            $paginator = $this->freshdeskClient->getTicketsIterator();

            /** @var string $ticketsJson */
            foreach ($paginator as $ticketsJson) {
                $this->backupStorage->savePage($ticketsJson, $backupId, $pageNumber);

                $tickets = json_decode($ticketsJson, true);
                if (is_array($tickets)) {
                    $ticketsCount = count($tickets);
                    $totalTickets += $ticketsCount;

                    if ($progress instanceof \Parser\Backup\Domain\BackupProgressInterface) {
                        $progress->reportPageSaved($pageNumber, $ticketsCount);
                    }
                }

                ++$pageNumber;
            }

            $metadata = [
                'created_at' => new DateTimeImmutable()->format('Y-m-d\TH:i:s\Z'),
                'total_tickets' => $totalTickets,
                'total_pages' => $pageNumber - 1,
            ];

            $this->backupStorage->saveMetadata($metadata, $backupId);

            return new BackupResponse(
                status: 'success',
                message: 'Бекап создан успешно',
                totalTickets: $totalTickets,
                filePath: 'backup_' . $backupId,
            );
        } catch (FreshdeskApiException $e) {
            return new BackupResponse(
                status: 'error',
                message: 'Ошибка подключения к Freshdesk API',
                errorDetails: $e->getMessage(),
            );
        } catch (BackupOperationFailedException $e) {
            return new BackupResponse(
                status: 'error',
                message: 'Ошибка записи на диск',
                errorDetails: $e->getMessage(),
            );
        } catch (\Throwable $e) {
            return new BackupResponse(
                status: 'error',
                message: 'Неизвестная ошибка при создании бекапа',
                errorDetails: $e->getMessage(),
            );
        }
    }
}

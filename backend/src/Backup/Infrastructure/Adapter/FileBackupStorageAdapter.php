<?php

declare(strict_types=1);

namespace Parser\Backup\Infrastructure\Adapter;

use Parser\Backup\Domain\BackupStorageInterface;
use Parser\Backup\Domain\Exception\BackupOperationFailedException;

final readonly class FileBackupStorageAdapter implements BackupStorageInterface
{
    public function __construct(
        private string $backupStoragePath,
    ) {
    }

    /**
     * @param string $ticketsJson Сырая JSON строка с задачами
     * @throws BackupOperationFailedException
     */
    public function savePage(string $ticketsJson, string $backupId, int $pageNumber): void
    {
        try {
            if (!is_dir($this->backupStoragePath) && !mkdir($this->backupStoragePath, 0755, true)) {
                throw new BackupOperationFailedException(
                    sprintf('Не удалось создать директорию: %s', $this->backupStoragePath),
                );
            }

            $filename = sprintf('backup_%s_page_%03d.json', $backupId, $pageNumber);
            $filePath = $this->backupStoragePath . '/' . $filename;

            if (file_put_contents($filePath, $ticketsJson) === false) {
                throw new BackupOperationFailedException(
                    sprintf('Не удалось записать файл: %s', $filePath),
                );
            }
        } catch (BackupOperationFailedException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new BackupOperationFailedException(
                sprintf('Ошибка при сохранении страницы бекапа: %s', $e->getMessage()),
                0,
                $e,
            );
        }
    }

    /**
     * @param array<string, mixed> $metadata
     * @throws BackupOperationFailedException
     */
    public function saveMetadata(array $metadata, string $backupId): void
    {
        try {
            if (!is_dir($this->backupStoragePath) && !mkdir($this->backupStoragePath, 0755, true)) {
                throw new BackupOperationFailedException(
                    sprintf('Не удалось создать директорию: %s', $this->backupStoragePath),
                );
            }

            $filename = sprintf('backup_%s_meta.json', $backupId);
            $filePath = $this->backupStoragePath . '/' . $filename;

            $json = json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            if ($json === false) {
                throw new BackupOperationFailedException(
                    'Не удалось сериализовать метаданные в JSON',
                );
            }

            if (file_put_contents($filePath, $json) === false) {
                throw new BackupOperationFailedException(
                    sprintf('Не удалось записать файл: %s', $filePath),
                );
            }
        } catch (BackupOperationFailedException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new BackupOperationFailedException(
                sprintf('Ошибка при сохранении метаданных бекапа: %s', $e->getMessage()),
                0,
                $e,
            );
        }
    }
}

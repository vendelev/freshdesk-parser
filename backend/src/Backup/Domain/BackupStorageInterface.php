<?php

declare(strict_types=1);

namespace Parser\Backup\Domain;

use Parser\Backup\Domain\Exception\BackupStorageWriteException;

interface BackupStorageInterface
{
    /**
     * @param string $ticketsJson Сырая JSON строка с задачами
     * @throws BackupStorageWriteException
     */
    public function savePage(string $ticketsJson, string $backupId, int $pageNumber): void;

    /**
     * @param array<string, mixed> $metadata
     * @throws BackupStorageWriteException
     */
    public function saveMetadata(array $metadata, string $backupId): void;
}

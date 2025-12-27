<?php

declare(strict_types=1);

namespace Parser\Backup\Domain;

interface BackupProgressInterface
{
    public function reportPageSaved(int $pageNumber, int $ticketsCount): void;
}

<?php

declare(strict_types=1);

namespace Parser\Backup\Presentation\Console\Progress;

use Illuminate\Console\Command;
use Parser\Backup\Domain\BackupProgressInterface;

final readonly class ConsoleBackupProgress implements BackupProgressInterface
{
    public function __construct(
        private Command $command,
    ) {
    }

    public function reportPageSaved(int $pageNumber, int $ticketsCount): void
    {
        $this->command->line("  → Страница {$pageNumber}: {$ticketsCount} задач сохранено");
    }
}

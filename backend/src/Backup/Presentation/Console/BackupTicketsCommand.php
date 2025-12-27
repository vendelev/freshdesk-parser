<?php

declare(strict_types=1);

namespace Parser\Backup\Presentation\Console;

use Illuminate\Console\Command;
use Parser\Backup\Application\UseCase\CreateBackupUseCase;
use Parser\Backup\Presentation\Console\Progress\ConsoleBackupProgress;

final class BackupTicketsCommand extends Command
{
    protected $signature = 'backup:tickets';

    protected $description = 'Создать бекап всех задач из Freshdesk';

    public function __construct(
        private readonly CreateBackupUseCase $createBackupUseCase,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Начало создания бекапа...');

        $progress = new ConsoleBackupProgress($this);
        $response = $this->createBackupUseCase->execute($progress);

        if ($response->status === 'success') {
            $this->newLine();
            $this->info('✓ Бекап создан успешно');
            $this->info("  Задач: {$response->totalTickets}");
            $this->info("  Путь: {$response->filePath}");

            return self::SUCCESS;
        }

        $this->newLine();
        $this->error("✗ {$response->message}");
        if ($response->errorDetails) {
            $this->error("  Подробности: {$response->errorDetails}");
        }

        return self::FAILURE;
    }
}

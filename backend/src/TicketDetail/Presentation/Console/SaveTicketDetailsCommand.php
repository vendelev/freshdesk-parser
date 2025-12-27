<?php

declare(strict_types=1);

namespace Parser\TicketDetail\Presentation\Console;

use Illuminate\Console\Command;
use Parser\TicketDetail\Application\UseCase\SaveTicketDetailUseCase;

final class SaveTicketDetailsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:ticket-details
                            {--ticket-id= : ID конкретной задачи для сохранения}
                            {--all : Сохранить детальную информацию для всех задач}
                            {--force : Принудительно перезаписать существующие файлы}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Сохранить детальную информацию задач из Freshdesk API';

    public function __construct(
        private readonly SaveTicketDetailUseCase $useCase,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $ticketId = $this->option('ticket-id');
        $all = $this->option('all');
        $force = $this->option('force');

        if ($ticketId === null && !$all) {
            $this->error('Необходимо указать либо --ticket-id, либо --all');
            return self::FAILURE;
        }

        if ($ticketId !== null && $all) {
            $this->error('Нельзя одновременно указывать --ticket-id и --all');
            return self::FAILURE;
        }

        try {
            if ($ticketId !== null) {
                // Сохранить одну задачу
                $response = $this->useCase->execute((int) $ticketId, $force);

                $this->displayResult($response);

                return $response->status === 'success' ? self::SUCCESS : self::FAILURE;
            }

            // Сохранить все задачи - пока заглушка, нужно получить список из Backup модуля
            $this->info('Функциональность --all пока не реализована. Используйте --ticket-id для конкретной задачи.');
            return self::FAILURE;
        } catch (\Throwable $e) {
            $this->error('Произошла ошибка: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Отобразить результат выполнения
     *
     * @param \Parser\TicketDetail\Domain\Response\SaveTicketDetailResponse $response Результат
     */
    private function displayResult(\Parser\TicketDetail\Domain\Response\SaveTicketDetailResponse $response): void
    {
        $statusIcon = match ($response->status) {
            'success' => '✅',
            'partial' => '⚠️',
            'failed' => '❌',
            default => '❓',
        };

        $this->line(sprintf(
            '%s Задача #%d: %s',
            $statusIcon,
            $response->ticketId,
            $response->status
        ));

        $this->line(sprintf('   Файл: %s', $response->filePath));
        $this->line(sprintf('   Сохранено: %s', $response->savedAt->format('Y-m-d H:i:s')));

        if ($response->errorMessage !== null) {
            $this->line(sprintf('   Ошибка: %s', $response->errorMessage));
        }
    }
}

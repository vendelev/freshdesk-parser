<?php

declare(strict_types=1);

namespace Parser\TicketDetail\Presentation\Console;

use Illuminate\Console\Command;
use Parser\TicketDetail\Application\UseCase\SaveAllTicketDetailsUseCase;
use Parser\TicketDetail\Application\UseCase\SaveTicketDetailUseCase;
use Parser\TicketDetail\Domain\Response\SaveTicketDetailResponse;
use Parser\TicketDetail\Domain\ValueObject\ProcessingSummary;

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
        private readonly SaveTicketDetailUseCase $saveTicketDetailUseCase,
        private readonly SaveAllTicketDetailsUseCase $saveAllTicketDetailsUseCase,
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
                $response = $this->saveTicketDetailUseCase->execute((int) $ticketId, $force);

                $this->displayResult($response);

                return $response->status === 'success' ? self::SUCCESS : self::FAILURE;
            }

            // Сохранить все задачи
            $summary = $this->saveAllTicketDetailsUseCase->execute($force);
            $this->displaySummary($summary);

            // Вернуть успех если обработано хотя бы одной задачи успешно или пропущено
            return $summary->processed > 0 || $summary->skipped > 0 ? self::SUCCESS : self::FAILURE;
        } catch (\Throwable $e) {
            $this->error('Произошла ошибка: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Отобразить результат выполнения
     *
     * @param SaveTicketDetailResponse $response Результат
     */
    private function displayResult(SaveTicketDetailResponse $response): void
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

    /**
     * Отобразить сводку выполнения
     *
     * @param ProcessingSummary $summary Сводка
     */
    private function displaySummary(ProcessingSummary $summary): void
    {
        $this->line('');
        $this->line('Сводка обработки:');
        $this->line(sprintf('  Всего задач: %d', $summary->totalTickets));
        $this->line(sprintf('  Обработано: %d', $summary->processed));
        $this->line(sprintf('  Пропущено: %d', $summary->skipped));
        $this->line(sprintf('  Ошибок: %d', $summary->failed));

        $dateTime = 'Не завершено';

        if ($summary->endTime instanceof \DateTimeImmutable) {
            $dateTime = $summary->startTime->diff($summary->endTime)->format('%H:%I:%S');
        }

        $this->line(sprintf('  Время выполнения: %s', $dateTime));
        $this->line('');
    }
}

<?php

declare(strict_types=1);

namespace Parser\Task\Presentation\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Parser\Task\Domain\Request\ImportTasksFromJsonRequest;
use Parser\Task\Application\UseCase\ImportTasksFromJson;
use RuntimeException;

/**
 * @final
 */
final class ImportJsonTaskCommand extends Command
{
    protected $signature = 'task:import-json {--path= : Path to directory with JSON files}';
    protected $description = 'Импорт заявок из JSON файлов Freshdesk';

    public function handle(ImportTasksFromJson $importTasksFromJsonUseCase): int
    {
        $path = $this->option('path') ?? 'backend/storage/freshdesk/';
        
        $this->info("Начало импорта заявок из JSON файлов в директории: {$path}");
        Log::info('Начало выполнения команды ImportJsonTaskCommand', ['path' => $path]);
        
        try {
            // Создание запроса для UseCase
            $request = new ImportTasksFromJsonRequest($path);
            
            // Вызов UseCase для обработки импорта
            $response = $importTasksFromJsonUseCase->run($request);
            
            // Вывод статистики выполнения
            $this->outputStatistics($response);
            
            Log::info('Команда ImportJsonTaskCommand успешно выполнена', [
                'total' => $response->totalTasks,
                'successful' => $response->successfulTasks,
                'errors' => $response->errorTasks,
                'duplicates' => $response->duplicateTasks
            ]);
            
            return Command::SUCCESS;
        } catch (RuntimeException $e) {
            $this->error('Ошибка при обработке JSON файлов: ' . $e->getMessage());
            Log::error('Ошибка при обработке JSON файлов: ' . $e->getMessage());
            return Command::FAILURE;
        } catch (\Exception $e) {
            $this->error('Непредвиденная ошибка: ' . $e->getMessage());
            Log::error('Непредвиденная ошибка: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
    
    private function outputStatistics($response): void
    {
        $this->info("=== Статистика обработки заявок ===");
        $this->info("Всего заявок: {$response->totalTasks}");
        $this->info("Успешно обработано: {$response->successfulTasks}");
        $this->info("Ошибок: {$response->errorTasks}");
        $this->info("Дубликатов: {$response->duplicateTasks}");
        $this->info("================================");
    }
}
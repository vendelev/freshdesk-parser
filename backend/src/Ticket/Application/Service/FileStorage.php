<?php

declare(strict_types=1);

namespace Parser\Ticket\Application\Service;

use Illuminate\Support\Facades\Log;

final readonly class FileStorage
{
    public function __construct(
        private string $basePath = '/var/www/backend/storage/tickets',
    ) {
    }

    /**
     * Сохранение списка задач
     *
     * @param array<string, mixed> $data Данные списка задач
     * @param int $page Номер страницы
     * @return void
     *
     * @throws \Exception
     */
    public function saveTicketsList(array $data, int $page): void
    {
        $dirPath = $this->basePath . '/list';
        $this->createDirectory($dirPath);

        $filePath = $dirPath . "/page_{$page}.json";

        try {
            $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            if ($json === false) {
                throw new \Exception('Failed to encode data to JSON');
            }
            file_put_contents($filePath, $json);
            Log::info("Saved tickets list", ['page' => $page, 'file' => $filePath]);
        } catch (\Throwable $e) {
            Log::error('Failed to save tickets list', [
                'page' => $page,
                'file' => $filePath,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Сохранение детальной информации о задаче
     *
     * @param array<string, mixed> $data Детальные данные задачи
     * @param int $ticketId ID задачи
     * @return void
     *
     * @throws \Exception
     */
    public function saveTicketDetails(array $data, int $ticketId): void
    {
        $dirPath = $this->basePath . '/detail';
        $this->createDirectory($dirPath);

        $filePath = $dirPath . "/{$ticketId}.json";

        try {
            $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            if ($json === false) {
                throw new \Exception('Failed to encode data to JSON');
            }

            file_put_contents($filePath, $json);
            Log::info("Saved ticket details", ['ticket_id' => $ticketId, 'file' => $filePath]);
        } catch (\Throwable $e) {
            Log::error('Failed to save ticket details', [
                'ticket_id' => $ticketId,
                'file' => $filePath,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Создание директории
     *
     * @param string $path Путь к директории
     * @return void
     *
     * @throws \Exception
     */
    public function createDirectory(string $path): void
    {
        if (!is_dir($path)) {
            try {
                mkdir($path, 0755, true);
                Log::info("Created directory", ['path' => $path]);
            } catch (\Throwable $e) {
                Log::error('Failed to create directory', [
                    'path' => $path,
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }
        }
    }
}

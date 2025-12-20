<?php

declare(strict_types=1);

namespace Parser\Task\Application\Service;

use Illuminate\Filesystem\Filesystem;
use Parser\Task\Domain\FreshdeskClientInterface;

final class LoadTicketDetailsService
{
    public function __construct(
        private readonly FreshdeskClientInterface $freshdeskClient,
        private readonly Filesystem $filesystem,
        private readonly string $basePath = 'freshdesk',
    ) {}

    /**
     * Загрузить детали для всех заявок из JSON файлов в storage/freshdesk.
     *
     * @return array<int, array{ticket_id: int, status: string, error?: string}>
     */
    public function loadAndSave(): array
    {
        $results = [];
        $jsonFiles = $this->findJsonFiles(storage_path($this->basePath));

        foreach ($jsonFiles as $filePath) {
            $results = array_merge($results, $this->processJsonFile($filePath));
        }

        return $results;
    }

    /**
     * Найти все JSON файлы в директории freshdesk.
     *
     * @return array<int, string>
     */
    private function findJsonFiles(string $directory): array
    {
        $files = [];

        if (!$this->filesystem->exists($directory)) {
            return $files;
        }

        $allFiles = $this->filesystem->allFiles($directory);

        foreach ($allFiles as $file) {
            if ($file->getExtension() === 'json') {
                $files[] = $file->getRealPath();
            }
        }

        return $files;
    }

    /**
     * Обработать один JSON файл и загрузить детали для каждой заявки.
     *
     * @return array<int, array{ticket_id: int, status: string, error?: string}>
     */
    private function processJsonFile(string $filePath): array
    {
        $results = [];
        $content = $this->filesystem->get($filePath);
        $tickets = json_decode($content, true, flags: JSON_THROW_ON_ERROR);

        if (!is_array($tickets)) {
            return $results;
        }

        $directory = dirname($filePath);

        foreach ($tickets as $ticket) {
            if (!isset($ticket['id'])) {
                continue;
            }

            $ticketId = $ticket['id'];

            try {
                $ticketDetails = $this->freshdeskClient->getTicket($ticketId);

                $detailsFile = "{$directory}/{$ticketId}.json";
                $json = json_encode($ticketDetails, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

                if ($json === false) {
                    throw new \RuntimeException('Failed to encode ticket details to JSON');
                }

                $this->filesystem->put($detailsFile, $json);

                $results[] = [
                    'ticket_id' => $ticketId,
                    'status' => 'success',
                ];
            } catch (\Throwable $e) {
                $results[] = [
                    'ticket_id' => $ticketId,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}

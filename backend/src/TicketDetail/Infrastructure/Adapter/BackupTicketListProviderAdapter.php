<?php

declare(strict_types=1);

namespace Parser\TicketDetail\Infrastructure\Adapter;

use Parser\TicketDetail\Domain\TicketListProviderInterface;
use RuntimeException;

final readonly class BackupTicketListProviderAdapter implements TicketListProviderInterface
{
    public function __construct(
        private string $backupStoragePath,
    ) {
    }

    /**
     * @return iterable<int> Итератор ID задач
     */
    public function getTicketIds(): iterable
    {
        // Проверить что директория существует
        if (!is_dir($this->backupStoragePath)) {
            throw new RuntimeException(
                sprintf('Директория бекапов не существует: %s', $this->backupStoragePath)
            );
        }

        // Найти все файлы бекапов страниц
        $pattern = $this->backupStoragePath . '/backup_*_page_*.json';
        $files = glob($pattern);

        if ($files === false) {
            throw new RuntimeException('Ошибка при поиске файлов бекапов');
        }

        $processedTicketIds = [];

        // Обработать каждый файл бекапа
        foreach ($files as $filePath) {
            // Прочитать содержимое файла
            $jsonContent = file_get_contents($filePath);

            if ($jsonContent === false) {
                // Пропустить файлы, которые не удалось прочитать
                continue;
            }

            // Декодировать JSON
            $tickets = json_decode($jsonContent, true);

            if (!is_array($tickets)) {
                // Пропустить файлы с некорректным форматом
                continue;
            }

            // Извлечь ID задач из каждой записи
            foreach ($tickets as $ticket) {
                if (is_array($ticket) && isset($ticket['id']) && is_int($ticket['id'])) {
                    $ticketId = $ticket['id'];

                    // Убедиться, что каждый ID возвращается только один раз
                    if (!in_array($ticketId, $processedTicketIds, true)) {
                        $processedTicketIds[] = $ticketId;
                        yield $ticketId;
                    }
                }
            }
        }
    }
}

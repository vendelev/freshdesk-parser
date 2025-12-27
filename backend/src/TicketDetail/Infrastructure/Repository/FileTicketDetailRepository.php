<?php

declare(strict_types=1);

namespace Parser\TicketDetail\Infrastructure\Repository;

use Parser\TicketDetail\Domain\Exception\TicketDetailSaveException;
use Parser\TicketDetail\Domain\TicketDetailRepositoryInterface;
use Parser\TicketDetail\Domain\ValueObject\TicketDetailMetadata;

final readonly class FileTicketDetailRepository implements TicketDetailRepositoryInterface
{
    private const string STORAGE_PATH = 'storage/backups/detail';

    public function __construct(
        private string $basePath = '',
    ) {
    }

    /**
     * Сохранить детальную информацию о задаче
     *
     * @param int $ticketId ID задачи
     * @param string $jsonData JSON данные задачи
     * @param TicketDetailMetadata $metadata Метаданные сохранения
     * @throws TicketDetailSaveException
     */
    public function save(int $ticketId, string $jsonData, TicketDetailMetadata $metadata): void
    {
        $filePath = $this->getFilePath($ticketId);

        try {
            // Создать директорию если не существует
            $directory = dirname($filePath);
            if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
                throw new TicketDetailSaveException(
                    sprintf('Не удалось создать директорию: %s', $directory)
                );
            }

            // Сохранить JSON данные
            $result = file_put_contents($filePath, $jsonData);
            if ($result === false) {
                throw new TicketDetailSaveException(
                    sprintf('Не удалось записать файл: %s', $filePath)
                );
            }

            // Проверить что файл действительно сохранен
            if (!file_exists($filePath)) {
                throw new TicketDetailSaveException(
                    sprintf('Файл не был создан: %s', $filePath)
                );
            }
        } catch (\Throwable $e) {
            if ($e instanceof TicketDetailSaveException) {
                throw $e;
            }

            throw TicketDetailSaveException::saveFailed($ticketId, $e->getMessage());
        }
    }

    /**
     * Проверить существует ли файл с детальной информацией задачи
     *
     * @param int $ticketId ID задачи
     * @return bool true если файл существует
     */
    public function exists(int $ticketId): bool
    {
        $filePath = $this->getFilePath($ticketId);

        return file_exists($filePath) && is_readable($filePath);
    }

    /**
     * Получить полный путь к файлу
     *
     * @param int $ticketId ID задачи
     * @return string Полный путь к файлу
     */
    private function getFilePath(int $ticketId): string
    {
        $relativePath = sprintf('%s/%d.json', self::STORAGE_PATH, $ticketId);

        if ($this->basePath !== '') {
            return $this->basePath . '/' . $relativePath;
        }

        return base_path($relativePath);
    }
}

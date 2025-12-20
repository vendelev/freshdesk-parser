<?php

declare(strict_types=1);

namespace Parser\Task\Application\Service;

use DateTimeImmutable;
use Illuminate\Filesystem\Filesystem;

final class TaskStorageService
{
    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly string $basePath = 'freshdesk',
    ) {}

    /**
     * Сохранить список задач в JSON файл.
     *
     * @param array<int, mixed> $tickets
     */
    public function save(array $tickets, DateTimeImmutable $dateTime): string
    {
        $year = $dateTime->format('Y');
        $month = $dateTime->format('m');
        $day = $dateTime->format('d');
        $time = $dateTime->format('Hi');

        $directory = storage_path("{$this->basePath}/{$year}/{$month}");

        if (!$this->filesystem->exists($directory)) {
            $this->filesystem->makeDirectory($directory, 0o755, true, true);
        }

        $filename = "{$day}_{$time}.json";
        $filepath = "{$directory}/{$filename}";

        $json = json_encode($tickets, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            throw new \RuntimeException('Failed to encode tasks to JSON');
        }

        $this->filesystem->put($filepath, $json);

        return $filepath;
    }
}

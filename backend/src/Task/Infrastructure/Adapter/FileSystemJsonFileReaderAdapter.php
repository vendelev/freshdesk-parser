<?php

declare(strict_types=1);

namespace Parser\Task\Infrastructure\Adapter;

use Parser\Task\Domain\JsonFileReaderInterface;
use Parser\Task\Domain\Dto\JsonFileData;
use RuntimeException;

/**
 * @final
 * @readonly
 */
final readonly class FileSystemJsonFileReaderAdapter implements JsonFileReaderInterface
{
    public function findJsonFiles(string $directory): array
    {
        if (!is_dir($directory)) {
            throw new RuntimeException("Directory does not exist: {$directory}");
        }

        if (!is_readable($directory)) {
            throw new RuntimeException("Directory is not readable: {$directory}");
        }

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
        $jsonFiles = [];

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'json') {
                $jsonFiles[] = $file->getPathname();
            }
        }

        return $jsonFiles;
    }

    public function readFile(string $filePath): JsonFileData
    {
        if (!is_file($filePath)) {
            throw new RuntimeException("File does not exist: {$filePath}");
        }

        if (!is_readable($filePath)) {
            throw new RuntimeException("File is not readable: {$filePath}");
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new RuntimeException("Failed to read file: {$filePath}");
        }

        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("Invalid JSON in file: {$filePath} - " . json_last_error_msg());
        }

        $fileName = basename($filePath);

        return new JsonFileData($filePath, $data, $fileName);
    }

    public function saveFile(string $filePath, array $data): bool
    {
        $directory = dirname($filePath);
        
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException("Failed to create directory: {$directory}");
        }

        $jsonContent = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($jsonContent === false) {
            throw new RuntimeException("Failed to encode data to JSON");
        }

        return file_put_contents($filePath, $jsonContent) !== false;
    }
}
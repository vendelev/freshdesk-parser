<?php

declare(strict_types=1);

namespace Parser\Task\Domain;

use Parser\Task\Domain\Dto\JsonFileData;

interface JsonFileReaderInterface
{
    /**
     * @param string $directory
     * @return array<int, string>
     */
    public function findJsonFiles(string $directory): array;
    
    /**
     * @param string $filePath
     * @return JsonFileData
     */
    public function readFile(string $filePath): JsonFileData;
    
    /**
     * @param string $filePath
     * @param array<mixed> $data
     * @return bool
     */
    public function saveFile(string $filePath, array $data): bool;
}
<?php

declare(strict_types=1);

namespace Parser\Task\Domain\Dto;

/**
 * @final
 * @readonly
 */
final readonly class JsonFileData
{
    public function __construct(
        public string $filePath,
        public array $content,
        public string $fileName,
    ) {
    }
}
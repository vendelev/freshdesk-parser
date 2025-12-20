<?php

declare(strict_types=1);

namespace Parser\Task\Domain;

interface TaskParserInterface
{
    public function parse(string $data): array;
}
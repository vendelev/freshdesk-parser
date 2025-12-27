<?php

declare(strict_types=1);

namespace Parser\TicketDetail\Domain\ValueObject;

use DateTimeImmutable;

final readonly class TicketDetailMetadata
{
    public function __construct(
        public DateTimeImmutable $savedAt,
        public string $status, // 'success'|'partial'|'failed'
        public int $size, // размер файла в байтах
    ) {
    }
}

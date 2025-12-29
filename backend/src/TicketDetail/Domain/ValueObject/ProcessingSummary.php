<?php

declare(strict_types=1);

namespace Parser\TicketDetail\Domain\ValueObject;

use DateTimeImmutable;

final readonly class ProcessingSummary
{
    public function __construct(
        public int $totalTickets,
        public int $processed,
        public int $skipped,
        public int $failed,
        public DateTimeImmutable $startTime,
        public ?DateTimeImmutable $endTime,
    ) {
    }
}

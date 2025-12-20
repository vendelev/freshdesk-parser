<?php

declare(strict_types=1);

namespace Parser\Task\Domain\Entity;

final class Task
{
    public function __construct(
        private readonly int $freshdeskId,
        private readonly string $subject,
        private readonly string $description,
        private readonly string $status,
        private readonly string $priority,
        private readonly int $requesterId,
        private readonly string $type,
        private readonly string $source,
        private readonly array $customFields = [],
    ) {}

    public function getFreshdeskId(): int
    {
        return $this->freshdeskId;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getPriority(): string
    {
        return $this->priority;
    }

    public function getRequesterId(): int
    {
        return $this->requesterId;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getCustomFields(): array
    {
        return $this->customFields;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->freshdeskId,
            'subject' => $this->subject,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'requester_id' => $this->requesterId,
            'type' => $this->type,
            'source' => $this->source,
            'custom_fields' => $this->customFields,
        ];
    }
}

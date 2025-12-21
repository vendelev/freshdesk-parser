<?php

declare(strict_types=1);

namespace Parser\Task\Domain\Exception;

use Exception;

final class FreshdeskApiException extends Exception
{
    public static function fromHttpCode(int $httpCode, string $message): self
    {
        return new self(sprintf('Freshdesk API error (%d): %s', $httpCode, $message));
    }

    public static function fromJsonError(string $error): self
    {
        return new self(sprintf('JSON parsing error: %s', $error));
    }
}

<?php

declare(strict_types=1);

namespace JustPush\Exceptions;

use RuntimeException;

/**
 * The API answered with an error status (401, 403, 404, 422, 429, …).
 *
 * Extends RuntimeException, so code that caught RuntimeException from earlier versions keeps working.
 */
class JustPushApiException extends RuntimeException
{
    private int $status;
    private ?array $body;

    public function __construct(string $message, int $status, ?array $body = null)
    {
        parent::__construct($message, $status);

        $this->status = $status;
        $this->body   = $body;
    }

    public static function fromResponse(string $action, int $status, ?array $body): self
    {
        return new self('Failed to ' . $action . ': ' . self::messageFrom($body, $status), $status, $body);
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getBody(): ?array
    {
        return $this->body;
    }

    /**
     * Per-field validation messages for a 422, e.g. ['sound' => ['The selected sound is invalid.']].
     */
    public function getErrors(): array
    {
        $errors = $this->body['errors'] ?? [];

        return is_array($errors) ? $errors : [];
    }

    public function isUnauthorized(): bool
    {
        return 401 === $this->status;
    }

    public function isValidationError(): bool
    {
        return 422 === $this->status;
    }

    public function isRateLimited(): bool
    {
        return 429 === $this->status;
    }

    /**
     * The API has a few error shapes: {"error": {"message": …}}, {"error": "…"} and {"message": …}.
     */
    private static function messageFrom(?array $body, int $status): string
    {
        $error = $body['error'] ?? null;

        if (is_array($error) && !empty($error['message'])) {
            return (string) $error['message'];
        }

        if (is_string($error) && '' !== $error) {
            return $error;
        }

        if (!empty($body['message']) && is_string($body['message'])) {
            return $body['message'];
        }

        return 'HTTP ' . $status;
    }
}

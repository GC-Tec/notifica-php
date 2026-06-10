<?php

namespace Notifica\Responses;

use DateTimeImmutable;
use JsonSerializable;

/**
 * A short-lived session token for a customer, used to authenticate WebSocket
 * and inbox requests from the client.
 */
class CustomerToken implements JsonSerializable
{
    public function __construct(
        public readonly string $token,
        public readonly DateTimeImmutable $expiresAt,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            token: (string) $payload['token'],
            expiresAt: new DateTimeImmutable((string) ($payload['expiresAt'] ?? $payload['expires_at'])),
        );
    }

    /**
     * @return array{token: string, expires_at: string}
     */
    public function jsonSerialize(): array
    {
        return [
            'token' => $this->token,
            'expires_at' => $this->expiresAt->format(DATE_ATOM),
        ];
    }
}

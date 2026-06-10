<?php

namespace Notifica\Responses;

use DateTimeImmutable;
use JsonSerializable;

/**
 * A single inbox notification delivered to a customer.
 *
 * The `data` property is an opaque, application-defined payload — the SDK never
 * interprets its shape.
 */
class CustomerNotification implements JsonSerializable
{
    /**
     * @param  array<string, mixed>|null  $data
     */
    public function __construct(
        public readonly string $id,
        public readonly ?string $title,
        public readonly string $body,
        public readonly ?array $data,
        public readonly ?DateTimeImmutable $readAt,
        public readonly DateTimeImmutable $createdAt,
    ) {}

    public function isRead(): bool
    {
        return $this->readAt !== null;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            id: (string) $payload['id'],
            title: isset($payload['title']) ? (string) $payload['title'] : null,
            body: (string) $payload['body'],
            data: $payload['data'] ?? null,
            readAt: isset($payload['read_at']) ? new DateTimeImmutable((string) $payload['read_at']) : null,
            createdAt: new DateTimeImmutable((string) $payload['created_at']),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'data' => $this->data,
            'read_at' => $this->readAt?->format(DATE_ATOM),
            'created_at' => $this->createdAt->format(DATE_ATOM),
        ];
    }
}

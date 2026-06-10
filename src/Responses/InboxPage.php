<?php

namespace Notifica\Responses;

use JsonSerializable;

/**
 * A paginated page of inbox notifications.
 */
class InboxPage implements JsonSerializable
{
    /**
     * @param  list<CustomerNotification>  $items
     */
    public function __construct(
        public readonly array $items,
        public readonly int $page,
        public readonly int $perPage,
        public readonly int $total,
        public readonly int $lastPage,
        public readonly bool $hasNextPage,
        public readonly bool $hasPreviousPage,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): self
    {
        /** @var array<string, mixed> $meta */
        $meta = $payload['meta'] ?? [];

        return new self(
            items: array_map(
                static fn (array $item) => CustomerNotification::fromArray($item),
                $payload['data'] ?? [],
            ),
            page: (int) ($meta['page'] ?? 1),
            perPage: (int) ($meta['per_page'] ?? 0),
            total: (int) ($meta['total'] ?? 0),
            lastPage: (int) ($meta['last_page'] ?? 1),
            hasNextPage: (bool) ($meta['has_next_page'] ?? false),
            hasPreviousPage: (bool) ($meta['has_previous_page'] ?? false),
        );
    }

    /**
     * @return array{data: list<array<string, mixed>>, meta: array<string, mixed>}
     */
    public function jsonSerialize(): array
    {
        return [
            'data' => array_map(
                static fn (CustomerNotification $item) => $item->jsonSerialize(),
                $this->items,
            ),
            'meta' => [
                'page' => $this->page,
                'per_page' => $this->perPage,
                'total' => $this->total,
                'last_page' => $this->lastPage,
                'has_next_page' => $this->hasNextPage,
                'has_previous_page' => $this->hasPreviousPage,
            ],
        ];
    }
}

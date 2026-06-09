<?php

namespace Notifica\Messages;

abstract class ChannelMessage implements Message
{
    protected ?string $title = null;
    protected ?string $body = null;
    protected array $data = [];

    public static function make(): static
    {
        return new static();
    }

    public function title(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function body(string $body): static
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function data(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function toPayload(): array
    {
        if ($this->body === null) {
            throw new \LogicException(static::class . ' requires a body.');
        }

        return array_filter([
            'title' => $this->title,
            'body' => $this->body,
            'data' => $this->data ?: null,
        ], fn ($v) => $v !== null);
    }
}

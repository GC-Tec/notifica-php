<?php

namespace Notifica;

class Target
{
    private function __construct(private readonly array $payload) {}

    public static function customer(string $externalId): self
    {
        return new self(['type' => 'customer', 'customer_external_id' => $externalId]);
    }

    public static function allDevices(): self
    {
        return new self(['type' => 'all_devices']);
    }

    public static function device(string $installationId): self
    {
        return new self(['type' => 'device_installation', 'device_installation_id' => $installationId]);
    }

    public static function emailAddress(string $email): self
    {
        return new self(['type' => 'email_address', 'email_address' => $email]);
    }

    public static function code(string $code): self
    {
        return new self(['type' => 'code', 'code' => $code]);
    }

    public static function tag(string ...$tags): self
    {
        return new self(['type' => 'tag', 'tags' => array_values($tags)]);
    }

    public static function customerTag(string $tag): self
    {
        return new self(['type' => 'customer_tag', 'tag' => $tag]);
    }

    public function toArray(): array
    {
        return $this->payload;
    }
}

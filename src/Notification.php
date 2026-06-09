<?php

namespace Notifica;

use DateTimeInterface;

abstract class Notification
{
    /**
     * Which channels this notification should be sent through.
     * Each entry must have a corresponding to{Channel}() method.
     *
     * @return list<'push'|'email'|'web'>
     */
    abstract public function via(): array;

    /**
     * Targets to send to when none are provided at send() time.
     * Override this to bake recipients into the notification class itself.
     *
     * @return list<Target>
     */
    public function targets(): array
    {
        return [];
    }

    /**
     * Schedule this notification for future delivery.
     * Return null to send immediately.
     */
    public function scheduledAt(): ?DateTimeInterface
    {
        return null;
    }

    /**
     * Idempotency key for this notification.
     * The SDK appends the channel name to keep each intent's code unique:
     * "my-code" → "my-code-push", "my-code-email", etc.
     */
    public function code(): ?string
    {
        return null;
    }
}

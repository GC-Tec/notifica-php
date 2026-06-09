<?php

namespace Notifica\Resources;

use DateTimeInterface;
use Notifica\Messages\Message;
use Notifica\Resource;
use Notifica\Target;

class NotificationsResource extends Resource
{
    /**
     * @param  list<Target>  $targets
     * @return array<string, mixed>
     */
    public function send(
        string $channel,
        Message $message,
        array $targets = [],
        ?DateTimeInterface $scheduledAt = null,
        ?string $code = null,
    ): array {
        $payload = array_filter([
            'channel' => $channel,
            'code' => $code,
            'scheduled_at' => $scheduledAt?->format(DateTimeInterface::ATOM),
            'targets' => $targets ? array_map(fn (Target $t) => $t->toArray(), $targets) : null,
            ...$message->toPayload(),
        ], fn ($v) => $v !== null);

        return $this->request('POST', 'notification-intents', ['json' => $payload]);
    }
}

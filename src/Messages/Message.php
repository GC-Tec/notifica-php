<?php

namespace Notifica\Messages;

interface Message
{
    /**
     * Returns the fields to merge into the notification intent payload.
     *
     * @return array<string, mixed>
     */
    public function toPayload(): array;
}

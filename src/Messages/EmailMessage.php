<?php

namespace Notifica\Messages;

class EmailMessage extends ChannelMessage
{
    private ?string $html = null;
    private ?string $to = null;
    private ?string $from = null;
    private ?string $fromDisplayName = null;

    /** Alias for title() — sets the email subject line. */
    public function subject(string $subject): static
    {
        return $this->title($subject);
    }

    public function html(string $html): static
    {
        $this->html = $html;

        return $this;
    }

    public function to(string $email): static
    {
        $this->to = $email;

        return $this;
    }

    public function from(string $email, ?string $displayName = null): static
    {
        $this->from = $email;
        $this->fromDisplayName = $displayName;

        return $this;
    }

    public function toPayload(): array
    {
        return array_filter([
            ...parent::toPayload(),
            'html' => $this->html,
            'to' => $this->to,
            'from' => $this->from,
            'from_display_name' => $this->fromDisplayName,
        ], fn ($v) => $v !== null);
    }
}

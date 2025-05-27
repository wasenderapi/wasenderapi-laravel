<?php

namespace WasenderApi\Events;

use Illuminate\Foundation\Events\Dispatchable;

class WasenderWebhookEvent
{
    use Dispatchable;

    public string $event;
    public array $payload;

    public function __construct(string $event, array $payload)
    {
        $this->event = $event;
        $this->payload = $payload;
    }
} 
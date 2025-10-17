<?php

namespace WasenderApi\Events;

use Illuminate\Foundation\Events\Dispatchable;

class MessageReceived
{
    use Dispatchable;

    public array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }
}

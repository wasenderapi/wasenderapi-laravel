<?php

namespace WasenderApi\Events;

use Illuminate\Foundation\Events\Dispatchable;

class QrCodeUpdated
{
    use Dispatchable;
    public array $payload;
    public function __construct(array $payload) { $this->payload = $payload; }
} 
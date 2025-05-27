<?php

namespace WasenderApi\Data;

/**
 * @property bool $enabled
 * @property int $maxRetries
 */
class RetryConfig
{
    public bool $enabled;
    public int $maxRetries;
    public function __construct(bool $enabled = false, int $maxRetries = 0)
    {
        $this->enabled = $enabled;
        $this->maxRetries = $maxRetries;
    }
} 
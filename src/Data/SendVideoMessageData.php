<?php

namespace WasenderApi\Data;

/**
 * @property string $to
 * @property string $videoUrl
 * @property string|null $text
 */
class SendVideoMessageData
{
    public string $to;
    public string $videoUrl;
    public ?string $text;
    public function __construct(string $to, string $videoUrl, ?string $text = null)
    {
        $this->to = $to;
        $this->videoUrl = $videoUrl;
        $this->text = $text;
    }
} 
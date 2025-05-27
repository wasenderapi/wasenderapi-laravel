<?php

namespace WasenderApi\Data;

/**
 * @property string $to
 * @property string $imageUrl
 * @property string|null $text
 */
class SendImageMessageData
{
    public string $to;
    public string $imageUrl;
    public ?string $text;
    public function __construct(string $to, string $imageUrl, ?string $text = null)
    {
        $this->to = $to;
        $this->imageUrl = $imageUrl;
        $this->text = $text;
    }
} 
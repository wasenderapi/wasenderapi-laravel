<?php

namespace WasenderApi\Data;

/**
 * @property string $to
 * @property string $stickerUrl
 */
class SendStickerMessageData
{
    public string $to;
    public string $stickerUrl;
    public function __construct(string $to, string $stickerUrl)
    {
        $this->to = $to;
        $this->stickerUrl = $stickerUrl;
    }
} 
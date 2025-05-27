<?php

namespace WasenderApi\Data;

/**
 * @property string $to
 * @property string $audioUrl
 */
class SendAudioMessageData
{
    public string $to;
    public string $audioUrl;
    public function __construct(string $to, string $audioUrl)
    {
        $this->to = $to;
        $this->audioUrl = $audioUrl;
    }
} 
<?php

namespace WasenderApi\Data;

class SendTextMessageData
{
    public string $to;
    public string $text;
    public function __construct(string $to, string $text)
    {
        $this->to = $to;
        $this->text = $text;
    }
} 
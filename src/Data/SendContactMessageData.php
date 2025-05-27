<?php

namespace WasenderApi\Data;

/**
 * @property string $to
 * @property string $contactName
 * @property string $contactPhone
 */
class SendContactMessageData
{
    public string $to;
    public string $contactName;
    public string $contactPhone;
    public function __construct(string $to, string $contactName, string $contactPhone)
    {
        $this->to = $to;
        $this->contactName = $contactName;
        $this->contactPhone = $contactPhone;
    }
} 
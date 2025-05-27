<?php

namespace WasenderApi\Data;

/**
 * @property string $to
 * @property string $documentUrl
 * @property string|null $text
 */
class SendDocumentMessageData
{
    public string $to;
    public string $documentUrl;
    public ?string $text;
    public function __construct(string $to, string $documentUrl, ?string $text = null)
    {
        $this->to = $to;
        $this->documentUrl = $documentUrl;
        $this->text = $text;
    }
} 
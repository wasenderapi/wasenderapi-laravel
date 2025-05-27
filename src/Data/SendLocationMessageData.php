<?php

namespace WasenderApi\Data;

/**
 * @property string $to
 * @property float $latitude
 * @property float $longitude
 * @property string|null $name
 * @property string|null $address
 */
class SendLocationMessageData
{
    public string $to;
    public float $latitude;
    public float $longitude;
    public ?string $name;
    public ?string $address;
    public function __construct(string $to, float $latitude, float $longitude, ?string $name = null, ?string $address = null)
    {
        $this->to = $to;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->name = $name;
        $this->address = $address;
    }
} 
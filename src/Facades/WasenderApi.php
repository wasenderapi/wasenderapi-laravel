<?php

namespace WasenderApi\Facades;

use Illuminate\Support\Facades\Facade;

class WasenderApi extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'wasenderapi.client';
    }
} 
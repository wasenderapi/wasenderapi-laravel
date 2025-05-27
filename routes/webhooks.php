<?php

use Illuminate\Support\Facades\Route;

Route::post(config('wasenderapi.webhook_route'), [
    WasenderApi\Http\Controllers\WebhookController::class,
    'handle',
]); 
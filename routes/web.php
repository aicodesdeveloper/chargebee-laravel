<?php

use Illuminate\Support\Facades\Route;
use AicodesDeveloper\Chargebee\Http\Controllers\WebhookController;

Route::post(
    config('chargebee.webhook_path', 'chargebee/webhook'),
    WebhookController::class
)->middleware(config('chargebee.webhook_middleware', ['api']));
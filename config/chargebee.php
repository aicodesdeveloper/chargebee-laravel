<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Chargebee API Credentials
    |--------------------------------------------------------------------------
    |
    | Your Chargebee site name and API key for authentication
    |
    */
    'site' => env('CHARGEBEE_SITE', ''),
    'api_key' => env('CHARGEBEE_API_KEY', ''),
    'webhook_secret' => env('CHARGEBEE_WEBHOOK_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the webhook URL path and middleware
    |
    */
    'webhook_path' => env('CHARGEBEE_WEBHOOK_PATH', 'chargebee/webhook'),
    'webhook_middleware' => ['api'],
    
    /*
    |--------------------------------------------------------------------------
    | Default Plans & Currency
    |--------------------------------------------------------------------------
    |
    | You can define your default subscription plans and currency
    |
    */
    'default_currency' => env('CHARGEBEE_CURRENCY', 'USD'),
    'plans' => [
        // Define your plans here
        // 'basic' => 'basic-monthly',
        // 'pro' => 'pro-monthly',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Model Associations
    |--------------------------------------------------------------------------
    |
    | Define which model represents a billable entity (like User)
    |
    */
    'billable_model' => \App\Models\User::class,
    'billable_column' => 'chargebee_id', // Column that stores Chargebee customer ID
    
    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Define cache settings for API responses
    |
    */
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // Cache time to live (in seconds)
    ],
];
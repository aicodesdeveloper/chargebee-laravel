# Chargebee Laravel Package

A Laravel package for integrating with Chargebee subscription services. Compatible with Laravel 12 and PHP 8.2+.

## Installation

You can install the package via composer:

```bash
composer require aicodesdeveloper/chargebee-laravel
```

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=chargebee-config
```

Add your Chargebee credentials to your `.env` file:

```
CHARGEBEE_SITE=your-site-name
CHARGEBEE_API_KEY=your-api-key
CHARGEBEE_WEBHOOK_SECRET=your-webhook-secret
```

Run the migrations to add the Chargebee customer ID column to your users table:

```bash
php artisan migrate
```

## Usage

Apply the `Billable` trait to your User model:

```php
use aicodesdeveloper\Chargebee\Traits\Billable;

class User extends Authenticatable
{
    use Billable;
    // ...
}
```

### Creating a customer

```php
$user = User::find(1);
$customer = $user->createOrUpdateAsChargebeeCustomer();
```

### Creating a subscription

```php
$user = User::find(1);
$subscription = $user->subscribe('pro-monthly');
```

### Get a checkout URL for hosted pages

```php
$user = User::find(1);
$checkoutUrl = $user->getCheckoutUrl('pro-monthly');
```

### Get customer's invoices

```php
$user = User::find(1);
$invoices = $user->invoices();
```

### Cancel a subscription

```php
$user = User::find(1);
$user->cancelSubscription('subscription_id', true); // true for end of term, false for immediate
```

## Webhooks

The package automatically registers a webhook route at `chargebee/webhook`. You can customize this in the config file.

Remember to add this URL to your Chargebee webhook settings: `https://your-app.com/chargebee/webhook`

## Security

If you discover any security vulnerabilities, please email [your-email] instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
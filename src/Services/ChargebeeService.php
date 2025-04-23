<?php

namespace AicodesDeveloper\Chargebee\Services;

use ChargeBee_Environment;
use ChargeBee_Customer;
use ChargeBee_Subscription;
use ChargeBee_Invoice;
use ChargeBee_Plan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ChargebeeService
{
    /**
     * Create a new Chargebee service instance.
     */
    public function __construct(
        protected string $apiKey,
        protected string $site,
        protected ?string $webhookSecret = null
    ) {
        ChargeBee_Environment::configure($this->site, $this->apiKey);
    }

    /**
     * Create or update a customer in Chargebee.
     */
    public function createOrUpdateCustomer(array $customerData): object
    {
        try {
            if (!empty($customerData['chargebee_id'])) {
                // Update existing customer
                $result = ChargeBee_Customer::update($customerData['chargebee_id'], [
                    'firstName' => $customerData['first_name'] ?? null,
                    'lastName' => $customerData['last_name'] ?? null,
                    'email' => $customerData['email'] ?? null,
                    'phone' => $customerData['phone'] ?? null,
                    'company' => $customerData['company'] ?? null,
                ])->request();
                
                return $result->customer();
            } else {
                // Create new customer
                $result = ChargeBee_Customer::create([
                    'firstName' => $customerData['first_name'] ?? null,
                    'lastName' => $customerData['last_name'] ?? null,
                    'email' => $customerData['email'] ?? null,
                    'phone' => $customerData['phone'] ?? null,
                    'company' => $customerData['company'] ?? null,
                ])->request();
                
                return $result->customer();
            }
        } catch (\Exception $e) {
            Log::error('Chargebee customer creation/update failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a subscription for a customer.
     */
    public function createSubscription(string $customerId, string $planId, array $options = []): object
    {
        try {
            $subscriptionData = [
                'planId' => $planId,
                'customerEmail' => $options['email'] ?? null,
                'customerId' => $customerId,
            ];
            
            if (!empty($options['addons'])) {
                $subscriptionData['addons'] = $options['addons'];
            }
            
            if (!empty($options['coupon'])) {
                $subscriptionData['couponIds'] = [$options['coupon']];
            }

            $result = ChargeBee_Subscription::create($subscriptionData)->request();
            return $result->subscription();
        } catch (\Exception $e) {
            Log::error('Chargebee subscription creation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Cancel a subscription.
     */
    public function cancelSubscription(string $subscriptionId, bool $endOfTerm = true): object
    {
        try {
            if ($endOfTerm) {
                $result = ChargeBee_Subscription::cancel($subscriptionId, [
                    'endOfTerm' => true
                ])->request();
            } else {
                $result = ChargeBee_Subscription::cancel($subscriptionId)->request();
            }
            
            return $result->subscription();
        } catch (\Exception $e) {
            Log::error('Chargebee subscription cancellation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get a list of available plans.
     */
    public function getPlans(): array
    {
        $cacheKey = 'chargebee:plans';
        
        if (config('chargebee.cache.enabled') && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        try {
            $result = ChargeBee_Plan::all([
                'limit' => 100,
                'status[is]' => 'active'
            ])->request();
            
            $plans = [];
            foreach ($result as $plan) {
                $plans[] = $plan->plan();
            }
            
            if (config('chargebee.cache.enabled')) {
                Cache::put($cacheKey, $plans, config('chargebee.cache.ttl', 3600));
            }
            
            return $plans;
        } catch (\Exception $e) {
            Log::error('Chargebee plans retrieval failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get customer's invoices.
     */
    public function getInvoices(string $customerId): array
    {
        try {
            $result = ChargeBee_Invoice::all([
                'customerId[is]' => $customerId,
                'limit' => 100,
            ])->request();
            
            $invoices = [];
            foreach ($result as $invoice) {
                $invoices[] = $invoice->invoice();
            }
            
            return $invoices;
        } catch (\Exception $e) {
            Log::error('Chargebee invoices retrieval failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Generate a hosted page URL for checkout.
     */
    public function getCheckoutUrl(string $planId, array $customerData = []): string
    {
        try {
            $params = [
                'subscription' => ['planId' => $planId],
            ];
            
            if (!empty($customerData)) {
                $params['customer'] = [
                    'firstName' => $customerData['first_name'] ?? null,
                    'lastName' => $customerData['last_name'] ?? null,
                    'email' => $customerData['email'] ?? null,
                ];
            }
            
            $result = \ChargeBee_HostedPage::checkoutNew($params)->request();
            return $result->hostedPage()->url;
        } catch (\Exception $e) {
            Log::error('Chargebee checkout URL generation failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Process a webhook from Chargebee.
     */
    public function handleWebhook(string $payload, string $signature): array
    {
        if ($this->webhookSecret) {
            // Validate webhook signature
            $computedSignature = hash_hmac('sha256', $payload, $this->webhookSecret);
            if (!hash_equals($computedSignature, $signature)) {
                Log::warning('Invalid Chargebee webhook signature');
                throw new \Exception('Invalid webhook signature');
            }
        }

        $eventData = json_decode($payload, true);
        $eventType = $eventData['event_type'] ?? null;
        
        // Process the webhook based on event type
        switch ($eventType) {
            case 'subscription_created':
            case 'subscription_cancelled':
            case 'subscription_changed':
            case 'subscription_renewed':
            case 'payment_succeeded':
            case 'payment_failed':
                // Handle the event
                // You might fire Laravel events here that your application can listen for
                event("chargebee.{$eventType}", [$eventData]);
                break;
        }
        
        return $eventData;
    }
}
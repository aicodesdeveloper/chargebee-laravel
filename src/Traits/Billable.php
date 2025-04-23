<?php

namespace AicodesDeveloper\Chargebee\Traits;

use AicodesDeveloper\Chargebee\Facades\Chargebee;

trait Billable
{
    /**
     * Create or update the customer in Chargebee.
     */
    public function createOrUpdateAsChargebeeCustomer(): object
    {
        $customerData = [
            'chargebee_id' => $this->{config('chargebee.billable_column', 'chargebee_id')},
            'first_name' => $this->first_name ?? null,
            'last_name' => $this->last_name ?? null,
            'email' => $this->email ?? null,
            'phone' => $this->phone ?? null,
            'company' => $this->company ?? null,
        ];

        $customer = Chargebee::createOrUpdateCustomer($customerData);
        
        // Update the model with the Chargebee customer ID if it doesn't have one
        if (empty($this->{config('chargebee.billable_column', 'chargebee_id')})) {
            $this->{config('chargebee.billable_column', 'chargebee_id')} = $customer->id;
            $this->save();
        }
        
        return $customer;
    }

    /**
     * Subscribe the user to a plan.
     */
    public function subscribe(string $planId, array $options = []): object
    {
        // Ensure the user exists as a customer in Chargebee
        if (empty($this->{config('chargebee.billable_column', 'chargebee_id')})) {
            $this->createOrUpdateAsChargebeeCustomer();
        }
        
        return Chargebee::createSubscription(
            $this->{config('chargebee.billable_column', 'chargebee_id')},
            $planId,
            array_merge(['email' => $this->email], $options)
        );
    }
    
    /**
     * Get the customer's invoices.
     */
    public function invoices(): array
    {
        if (empty($this->{config('chargebee.billable_column', 'chargebee_id')})) {
            return [];
        }
        
        return Chargebee::getInvoices($this->{config('chargebee.billable_column', 'chargebee_id')});
    }
    
    /**
     * Cancel the customer's subscription.
     */
    public function cancelSubscription(string $subscriptionId, bool $endOfTerm = true): object
    {
        return Chargebee::cancelSubscription($subscriptionId, $endOfTerm);
    }
    
    /**
     * Generate a checkout URL for this customer.
     */
    public function getCheckoutUrl(string $planId): string
    {
        $customerData = [
            'first_name' => $this->first_name ?? null,
            'last_name' => $this->last_name ?? null,
            'email' => $this->email ?? null,
        ];
        
        return Chargebee::getCheckoutUrl($planId, $customerData);
    }
}
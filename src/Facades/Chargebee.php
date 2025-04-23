<?php

namespace AicodesDeveloper\Chargebee\Facades;

use Illuminate\Support\Facades\Facade;

class Chargebee extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'chargebee';
    }
}
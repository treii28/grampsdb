<?php

namespace Treii28\Grampsdb\Facades;

use Illuminate\Support\Facades\Facade;

class Grampsdb extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'grampsdb';
    }
}

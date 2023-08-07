<?php namespace Seiger\sSeo\Facades;

use Illuminate\Support\Facades\Facade;

class sSeo extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'sSeo';
    }
}
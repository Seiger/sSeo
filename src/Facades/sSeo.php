<?php namespace Seiger\sSeo\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class sSeo
 *
 * This class is a facade for the sSeo component, which allows easy access to its functionality.
 *
 * @see Facade
 */
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
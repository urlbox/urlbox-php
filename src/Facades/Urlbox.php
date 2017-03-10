<?php

namespace Urlbox\Screenshots\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Datatables.
 *
 * @package Urlbox\Screenshots\Facades
 * @author Chris Roebuck <chris@urlbox.io>
 */
class Urlbox extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'urlbox';
    }
}

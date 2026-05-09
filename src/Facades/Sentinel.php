<?php

namespace Chrisquices\Sentinel\Facades;

use Illuminate\Support\Facades\Facade;

class Sentinel extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'sentinel';
    }
}

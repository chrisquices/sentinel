<?php

namespace Chrisquices\VulcanSentinel\Facades;

use Illuminate\Support\Facades\Facade;

class Sentinel extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'vulcan-sentinel';
    }
}

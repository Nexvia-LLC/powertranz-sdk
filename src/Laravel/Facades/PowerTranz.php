<?php

declare(strict_types=1);

namespace PowerTranz\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use PowerTranz\PowerTranzClient;

/**
 * @mixin PowerTranzClient
 */
final class PowerTranz extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PowerTranzClient::class;
    }
}

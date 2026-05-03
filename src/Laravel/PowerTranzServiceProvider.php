<?php

declare(strict_types=1);

namespace PowerTranz\Laravel;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use PowerTranz\PowerTranzClient;
use PowerTranz\PowerTranzConfig;

final class PowerTranzServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__, 2) . '/config/powertranz.php',
            'powertranz'
        );

        $this->app->singleton(PowerTranzConfig::class, function ($app): PowerTranzConfig {
            /** @var array<string, mixed> $cfg */
            $cfg = $app['config']->get('powertranz', []);

            return PowerTranzConfig::fromArray($cfg);
        });

        $this->app->singleton(PowerTranzClient::class, function ($app): PowerTranzClient {
            return new PowerTranzClient($app->make(PowerTranzConfig::class));
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                dirname(__DIR__, 2) . '/config/powertranz.php' => config_path('powertranz.php'),
            ], 'powertranz-config');
        }
    }

    /**
     * @return array<int, class-string>
     */
    public function provides(): array
    {
        return [PowerTranzConfig::class, PowerTranzClient::class];
    }
}

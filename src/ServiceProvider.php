<?php

declare(strict_types=1);

namespace Cndrsdrmn\LaravelFailures;

use Cndrsdrmn\LaravelFailures\Contracts\MetaThrowable;
use Cndrsdrmn\LaravelFailures\Types\Meta;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

final class ServiceProvider extends IlluminateServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(MetaThrowable::class, Meta::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

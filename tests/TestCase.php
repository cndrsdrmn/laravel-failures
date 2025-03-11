<?php

declare(strict_types=1);

namespace Tests;

use Cndrsdrmn\LaravelFailures\ServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string>
     *
     * @api
     */
    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }
}

<?php

declare(strict_types=1);

namespace Tests;

use Cndrsdrmn\LaravelFailures\ServiceProvider;
use Exception;
use Orchestra\Testbench\TestCase as BaseTestCase;
use ReflectionException;
use ReflectionObject;

abstract class TestCase extends BaseTestCase
{
    /**
     * Overrides the stack trace of the given exception.
     *
     * @throws ReflectionException
     */
    final public function overrideStacktraceException(Exception &$exception, array $parameters = []): void
    {
        $reflection = new ReflectionObject($exception);

        while ($reflection->getParentClass() !== false) {
            $reflection = $reflection->getParentClass();
        }

        $traceReflection = $reflection->getProperty('trace');
        $traceReflection->setAccessible(true);
        $traceReflection->setValue($exception, $parameters);
        $traceReflection->setAccessible(false);
    }

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

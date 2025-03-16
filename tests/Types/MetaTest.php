<?php

declare(strict_types=1);

namespace Tests\Types;

use Carbon\Carbon;
use Cndrsdrmn\LaravelFailures\Contracts\TraceableStack;
use Cndrsdrmn\LaravelFailures\Failure;
use Cndrsdrmn\LaravelFailures\Types\Meta;
use Exception;
use Illuminate\Support\Facades\Date;
use Mockery;

beforeEach(function (): void {
    $this->timestamp = '2025-03-15T00:00:00';
    $this->tracer = 'fakeuuid';

    $carbon = Mockery::mock(Carbon::class, ['toISOString' => $this->timestamp]);
    Date::shouldReceive('now')->andReturn($carbon);

    Failure::createTracerUsing(fn (): string => $this->tracer);
});

afterEach(function (): void {
    Failure::createTracerNormally();
});

test('create using default value args', function (): void {
    expect(new Meta)
        ->stacktrace()->toBeNull()
        ->timestamp()->toBe($this->timestamp)
        ->tracer()->toBe($this->tracer)
        ->throwable()->toBeNull()
        ->resolve()->toEqualCanonicalizing([
            'timestamp' => $this->timestamp,
            'trace_id' => $this->tracer,
        ])
        ->toArray()->toEqualCanonicalizing([
            'timestamp' => $this->timestamp,
            'trace_id' => $this->tracer,
        ]);
});

test('create using throwable with debug true', function (): void {
    config(['app.debug' => true]);

    $exception = new Exception;
    $this->overrideStacktraceException($exception, [
        ['class' => 'Foo', 'function' => 'backtrace', 'line' => 10, 'file' => __FILE__],
        ['class' => 'Bar', 'function' => 'trace', 'line' => 25, 'file' => base_path('vendor/package/Baz.php')],
        ['class' => 'Baz', 'function' => 'tracer', 'line' => 40],
    ]);

    $stacktrace = [
        ['function' => 'Foo::backtrace', 'file' => __FILE__, 'line' => $exception->getLine(), 'in_app' => true],
        ['function' => 'Bar::trace', 'file' => __FILE__, 'line' => 10, 'in_app' => true],
        ['function' => 'Baz::tracer', 'file' => base_path('vendor/package/Baz.php'), 'line' => 25, 'in_app' => false],
        ['function' => null, 'file' => 'internal', 'line' => 40, 'in_app' => false],
    ];

    $meta = new Meta($exception);

    expect($meta)
        ->stacktrace()->toBeInstanceOf(TraceableStack::class)
        ->timestamp()->toBe($this->timestamp)
        ->tracer()->toBe($this->tracer)
        ->throwable()->toBe($exception)
        ->resolve()->toEqualCanonicalizing([
            'timestamp' => $this->timestamp,
            'trace_id' => $this->tracer,
            'exception' => $exception::class,
            'most_relevant_stacktrace' => $stacktrace[0],
            'stacktrace' => $stacktrace,
        ])
        ->toArray()->toEqualCanonicalizing([
            'timestamp' => $this->timestamp,
            'trace_id' => $this->tracer,
            'exception' => $exception::class,
            'most_relevant_stacktrace' => $stacktrace[0],
            'stacktrace' => $stacktrace,
        ]);
});

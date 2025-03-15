<?php

declare(strict_types=1);

namespace Tests\Types;

use Cndrsdrmn\LaravelFailures\Contracts\TraceableFrame;
use Cndrsdrmn\LaravelFailures\Types\Stacktrace;
use Exception;
use Mockery;
use UnexpectedValueException;

beforeEach(function (): void {
    $this->tracer = [
        ['class' => 'foo', 'function' => 'backtrace', 'line' => __LINE__, 'file' => __FILE__],
        ['class' => 'bar', 'function' => 'trace', 'line' => 25, 'file' => base_path('vendor/baz')],
        ['class' => 'baz', 'function' => 'tracer', 'line' => 40],
    ];

    $this->expectedFrames = [
        [
            'function' => 'funcOne',
            'file' => 'FileOne.php',
            'line' => 20,
            'in_app' => true,
        ],
        [
            'function' => 'funcTwo',
            'file' => base_path('vendor/package/FileTwo.php'),
            'line' => 20,
            'in_app' => false,
        ],
    ];
});

it('can create an instance from throwable', function (): void {
    $exception = new Exception;
    $this->overrideStacktraceException($exception, $this->tracer);

    $stacktrace = Stacktrace::createFromThrowable($exception);

    expect($stacktrace->all())->toHaveCount(count($this->tracer) + 1);
});

it('can create an instance from backtrace', function (): void {
    $stacktrace = Stacktrace::createFromBacktrace($this->tracer, __FILE__, __LINE__);

    expect($stacktrace->all())->toHaveCount(count($this->tracer) + 1);
});

test('cannot create an instance with empty frames', function (): void {
    $instance = fn (): Stacktrace => new Stacktrace([]);

    expect($instance)->toThrow(
        UnexpectedValueException::class,
        'Stacktrace cannot be empty.'
    );
});

test('cannot create an instance with invalid frame item', function (): void {
    $instance = fn (): Stacktrace => new Stacktrace(['invalid_frame']);

    expect($instance)->toThrow(
        UnexpectedValueException::class,
        sprintf("Collection should only include [%s] items, but 'string' found at position 0.", TraceableFrame::class)
    );
});

test('should be returns correct relevant frame', function (): void {
    $one = Mockery::mock(TraceableFrame::class, ['inApp' => false]);
    $two = Mockery::mock(TraceableFrame::class, ['inApp' => true]);

    $stacktrace = new Stacktrace([$one, $two]);

    expect($stacktrace->firstRelevantFrame())->toBe($two);
});

test('should be returns first frame item if no relevant frame', function (): void {
    $one = Mockery::mock(TraceableFrame::class, ['inApp' => false]);
    $two = Mockery::mock(TraceableFrame::class, ['inApp' => false]);

    $stacktrace = new Stacktrace([$one, $two]);

    expect($stacktrace->firstRelevantFrame())->toBe($one);
});

test('resolve should be returns an array of traceable frame', function (): void {
    [$arrayOne, $arrayTwo] = $this->expectedFrames;

    $one = Mockery::mock(TraceableFrame::class, ['resolve' => $arrayOne]);
    $two = Mockery::mock(TraceableFrame::class, ['resolve' => $arrayTwo]);

    $stacktrace = new Stacktrace([$one, $two]);

    expect($stacktrace->resolve())->toEqualCanonicalizing($this->expectedFrames);
});

test('returns an array of traceable frame', function (): void {
    [$arrayOne, $arrayTwo] = $this->expectedFrames;

    $one = Mockery::mock(TraceableFrame::class, ['toArray' => $arrayOne]);
    $two = Mockery::mock(TraceableFrame::class, ['toArray' => $arrayTwo]);

    $stacktrace = new Stacktrace([$one, $two]);

    expect($stacktrace->toArray())->toEqualCanonicalizing($this->expectedFrames);
});

<?php

declare(strict_types=1);

namespace Tests;

use AssertionError;
use Carbon\Carbon;
use Cndrsdrmn\LaravelFailures\Failure;
use Illuminate\Support\Facades\Date;
use Mockery;

beforeEach(function (): void {
    Failure::createTimestampNormally();
});

test('force render is initially false', function (): void {
    expect(Failure::isForceRender())->toBeFalse();
});

test('force render can configured', function (): void {
    Failure::shouldForceRender();

    expect(Failure::isForceRender())->toBeTrue();

    Failure::shouldForceRender(false);

    expect(Failure::isForceRender())->toBeFalse();
});

test('meta wrapper return default initialize', function (): void {
    expect(Failure::metaWrapper())->toBe('meta');
});

test('meta wrapper can be changed', function (): void {
    Failure::wrapMetaUsing('context');

    expect(Failure::metaWrapper())->toBe('context');
});

test('wrapper return default initialize', function (): void {
    expect(Failure::wrapper())->toBe('errors');
});

test('wrapper can be changed', function (): void {
    Failure::wrapUsing('failures');

    expect(Failure::wrapper())->toBe('failures');
});

test('timestamp uses default behavior', function (): void {
    $carbon = Mockery::mock(Carbon::class, ['toISOString' => '2025-01-01T00:00:00.168134Z']);
    Date::shouldReceive('now')->andReturn($carbon);

    $timestamp = Failure::timestamp();

    expect($timestamp)->toBe('2025-01-01T00:00:00.168134Z');
});

test('timestamp uses custom', function ($custom): void {
    Failure::createTimestampUsing(fn () => $custom);

    $timestamp = Failure::timestamp();

    expect($timestamp)->toEqual($custom);
})->with([
    'string' => 'custom-timestamp',
    'numeric' => 1234567890,
]);

test('timestamp uses custom with invalid return type', function (): void {
    Failure::createTimestampUsing(fn (): array => ['invalid']);

    $timestamp = fn (): string => Failure::timestamp();

    expect($timestamp)->toThrow(AssertionError::class, 'The timestamp type should be string or numeric.');
});

test('timestamp can be reset to default', function (): void {
    $carbon = Mockery::mock(Carbon::class, ['toISOString' => '2025-01-01T00:00:00.168134Z']);
    Date::shouldReceive('now')->andReturn($carbon);

    Failure::createTimestampUsing(fn (): string => 'custom-timestamp');
    Failure::createTimestampNormally();

    $timestamp = Failure::timestamp();

    expect($timestamp)->toBe('2025-01-01T00:00:00.168134Z');
});

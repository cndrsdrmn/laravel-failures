<?php

declare(strict_types=1);

namespace Tests;

use Cndrsdrmn\LaravelFailures\Failure;

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

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

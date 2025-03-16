<?php

declare(strict_types=1);

namespace Tests\Types;

use Cndrsdrmn\LaravelFailures\Types\Violation;

test('handles default values', function (): void {
    $issue = new Violation;

    expect($issue)
        ->attribute()->toBe('internal')
        ->message()->toBe('An unexpected error occurred.')
        ->prefix()->toBe('unknown')
        ->violated()->toBe('system');
});

test('handles custom values', function (): void {
    $issue = new Violation('Custom message.', 'custom', 'custom', '/custom/to/prefix');

    expect($issue)
        ->attribute()->toBe('custom')
        ->message()->toBe('Custom message.')
        ->prefix()->toBe('/custom/to/prefix')
        ->violated()->toBe('custom');
});

test('can be converted to array', function (): void {
    $issue = new Violation;

    expect($issue->toArray())->toEqualCanonicalizing([
        'message' => 'An unexpected error occurred.',
        'attribute' => 'internal',
        'prefix' => 'unknown',
        'violated' => 'system',
    ]);
});

test('resolve returns an array', function (): void {
    $issue = new Violation;

    expect($issue->resolve())->toEqualCanonicalizing([
        'message' => 'An unexpected error occurred.',
        'attribute' => 'internal',
        'prefix' => 'unknown',
        'violated' => 'system',
    ]);
});

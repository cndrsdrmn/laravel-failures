<?php

declare(strict_types=1);

namespace Tests\Types;

use Cndrsdrmn\LaravelFailures\Contracts\IssueDescriptor;
use Cndrsdrmn\LaravelFailures\Types\Breakdown;
use Illuminate\Support\Collection;
use Mockery;
use UnexpectedValueException;

test('creates with an empty errors', function (): void {
    $breakdown = new Breakdown;

    expect($breakdown->all())->toBeEmpty();
});

test('creates with non-empty errors', function (): void {
    $issue = Mockery::mock(IssueDescriptor::class);
    $breakdown = new Breakdown([$issue]);

    expect($breakdown->all())->toHaveCount(1);
});

test('can resolve correct errors', function (): void {
    $issue = Mockery::mock(IssueDescriptor::class, [
        'resolve' => ['violated' => 'unknown'],
    ]);

    $breakdown = new Breakdown([$issue]);

    expect($breakdown->resolve())->toEqualCanonicalizing([
        ['violated' => 'unknown'],
    ]);
});

test('can converts to array', function (): void {
    $issue = Mockery::mock(IssueDescriptor::class, [
        'toArray' => ['violated' => 'unknown'],
    ]);

    $breakdown = new Breakdown([$issue]);

    expect($breakdown->toArray())->toEqualCanonicalizing([
        ['violated' => 'unknown'],
    ]);
});

test('wraps an issue descriptor', function (): void {
    $issue = Mockery::mock(IssueDescriptor::class);

    $breakdown = Breakdown::wrap($issue);

    expect($breakdown->all())->toHaveCount(1);
});

test('wraps an issue descriptor with array', function (): void {
    $issue = Mockery::mock(IssueDescriptor::class);

    $breakdown = Breakdown::wrap([$issue]);

    expect($breakdown->all())->toHaveCount(1);
});

test('wraps an issue descriptor with enumerable', function (): void {
    $issue = Mockery::mock(IssueDescriptor::class, ['toArray' => ['violated' => 'unknown']]);

    $breakdown = Breakdown::wrap(Collection::make([$issue]));

    expect($breakdown->all())->toHaveCount(1);
});

test('wraps an issue descriptor with current instance', function (): void {
    $issue = Mockery::mock(IssueDescriptor::class);
    $expected = new Breakdown([$issue]);

    $breakdown = Breakdown::wrap($expected);

    expect($breakdown->all())->toHaveCount(1);
});

test('throws exception for invalid wrap', function (): void {
    $breakdown = fn (): Breakdown => Breakdown::wrap('invalid');

    expect($breakdown)->toThrow(UnexpectedValueException::class, 'Cannot wrap the issue of type: string.');
});

<?php

declare(strict_types=1);

namespace Tests\Types;

use Cndrsdrmn\LaravelFailures\Contracts\IssueDescriptor;
use Cndrsdrmn\LaravelFailures\Types\Breakdown;
use Cndrsdrmn\LaravelFailures\Types\Violation;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Exceptions\BackedEnumCaseNotFoundException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rules\ImageFile;
use Illuminate\Validation\ValidationException;
use Mockery;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;
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

test('can get default response', function (): void {
    $breakdown = Breakdown::wrap(Mockery::mock(IssueDescriptor::class));

    expect($breakdown)
        ->message()->toBe('Whoops, looks like something went wrong.')
        ->status()->toBe(500);
});

test('can sets message and status for response', function (): void {
    $breakdown = Breakdown::wrap(Mockery::mock(IssueDescriptor::class));
    $breakdown = $breakdown
        ->withMessage('Update message from Breakdown')
        ->withStatus(404);

    expect($breakdown)
        ->message()->toBe('Update message from Breakdown')
        ->status()->toBe(404);
});

it('can handle authentication exception', function (): void {
    $breakdown = Breakdown::auth(new AuthenticationException);

    expect($breakdown)
        ->message()->toBe('Unauthenticated.')
        ->all()->toHaveCount(1)
        ->toArray()->toEqualCanonicalizing([
            [
                'attribute' => 'Authorization',
                'message' => 'Missing or invalid authentication token.',
                'prefix' => 'header',
                'violated' => 'authentication',
            ],
        ]);
});

it('can handle http exception interface', function (Throwable $exception, array $expected): void {
    $breakdown = Breakdown::http($exception);

    expect($breakdown)
        ->message()->toBe($expected['message'])
        ->status()->toBe($expected['status'])
        ->all()->toHaveCount(1)
        ->toArray()->toEqualCanonicalizing([$expected['to_array']]);
})->with([
    'default' => fn (): array => [
        HttpException::fromStatusCode(400),
        [
            'message' => 'Bad Request',
            'status' => 400,
            'to_array' => [
                'attribute' => 'internal',
                'message' => 'An unexpected error occurred.',
                'prefix' => 'resource',
                'violated' => 'request',
            ],
        ],
    ],
    'backend enum' => fn (): array => [
        HttpException::fromStatusCode(404, previous: new BackedEnumCaseNotFoundException('foo', 'bar')),
        [
            'message' => 'Not Found',
            'status' => 404,
            'to_array' => [
                'attribute' => 'internal',
                'message' => 'The requested resource could not be found.',
                'prefix' => 'resource',
                'violated' => 'request',
            ],
        ],
    ],
    'model' => fn (): array => [
        HttpException::fromStatusCode(404, previous: new ModelNotFoundException),
        [
            'message' => 'Not Found',
            'status' => 404,
            'to_array' => [
                'attribute' => 'internal',
                'message' => 'The requested resource could not be found.',
                'prefix' => 'resource',
                'violated' => 'request',
            ],
        ],
    ],
    'token' => fn (): array => [
        HttpException::fromStatusCode(419, previous: new TokenMismatchException),
        [
            'message' => 'Whoops, looks like something went wrong.',
            'status' => 419,
            'to_array' => [
                'attribute' => 'internal',
                'message' => 'The provided token is invalid or has expired.',
                'prefix' => 'resource',
                'violated' => 'request',
            ],
        ],
    ],
]);

it('can handle throwable exception', function (): void {
    $breakdown = Breakdown::throwable(new UnexpectedValueException('Unexpected value.'));

    expect($breakdown)
        ->all()->toHaveCount(1)
        ->toArray()->toEqualCanonicalizing([
            [
                'message' => 'Unexpected value.',
                'attribute' => 'internal',
                'violated' => 'system',
                'prefix' => 'unknown',
            ],
        ]);
});

it('can handle validation exception', function (): void {
    $validator = validator([
        'foo' => '',
        'bar' => 'file',
    ], [
        'foo' => 'required',
        'bar' => ImageFile::default(),
    ]);

    $exception = new ValidationException($validator);

    $breakdown = Breakdown::validation($exception);

    expect($breakdown)
        ->all()->toHaveCount(2)
        ->message()->toBe($exception->getMessage());
});

it('can be render as response', function (): void {
    $breakdown = Breakdown::wrap(new Violation('Something an error.'));

    expect($breakdown)
        ->response()->toBeInstanceOf(JsonResponse::class)
        ->toResponse(app()->make('request'))->toBeInstanceOf(JsonResponse::class);
});

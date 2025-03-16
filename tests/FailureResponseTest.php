<?php

declare(strict_types=1);

namespace Tests;

use Cndrsdrmn\LaravelFailures\Contracts\ErrorProvider;
use Cndrsdrmn\LaravelFailures\Contracts\MetaThrowable;
use Cndrsdrmn\LaravelFailures\FailureResponse;
use Cndrsdrmn\LaravelFailures\Types\Breakdown;
use Cndrsdrmn\LaravelFailures\Types\Meta;
use Cndrsdrmn\LaravelFailures\Types\Violation;
use Exception;
use Illuminate\Http\JsonResponse;
use Mockery;

it('render response with a default behaviour response', function (): void {
    $errors = Breakdown::wrap(new Violation);

    $response = $this->createTestResponse(new FailureResponse($errors));

    $response
        ->assertInternalServerError()
        ->assertJsonStructure([
            'message',
            'errors' => ['*' => ['attribute', 'message', 'prefix', 'violated']],
            'meta' => ['timestamp', 'trace_id'],
        ]);
});

it('render response with a customize message, status, meta info, and debug true', function (): void {
    config(['app.debug' => true]);

    $errors = Breakdown::wrap(new Violation('Error'));
    $meta = new Meta(new Exception('Something gonna be wrong.'));

    $response = $this->createTestResponse(
        new FailureResponse($errors, 'Custom message', 400, $meta)
    );

    $response
        ->assertBadRequest()
        ->assertJsonStructure([
            'message',
            'errors' => ['*' => ['attribute', 'message', 'prefix', 'violated']],
            'meta' => ['timestamp', 'trace_id', 'exception', 'most_relevant_stacktrace', 'stacktrace'],
        ])
        ->assertJson([
            'message' => 'Custom message',
            'errors' => [['message' => 'Error']],
            'meta' => ['exception' => 'Exception'],
        ]);
});

it('render response with override meta throwable', function (): void {
    config(['app.debug' => true]);

    $errors = Breakdown::wrap(new Violation);

    $response = $this->createTestResponse(
        (new FailureResponse($errors))->withThrowable(new Exception('Something gonna be wrong.'))
    );

    $response
        ->assertInternalServerError()
        ->assertJsonStructure([
            'meta' => ['timestamp', 'trace_id', 'exception', 'most_relevant_stacktrace', 'stacktrace'],
        ])
        ->assertJson([
            'meta' => ['exception' => 'Exception'],
        ]);
});

test('can override the message for response', function (): void {
    $response = new FailureResponse(
        Mockery::mock(ErrorProvider::class), 'Initial message'
    );

    $response = $response->withMessage('Update message');

    expect($response->message())->toBe('Update message');
});

test('can override the status for response', function (): void {
    $response = new FailureResponse(
        Mockery::mock(ErrorProvider::class), status: 400
    );

    $response = $response->withStatus(422);

    expect($response->status())->toBe(422);
});

test('only accept status between 400 to 503', function (): void {
    $response = new FailureResponse(
        Mockery::mock(ErrorProvider::class), status: 200
    );

    expect($response->status())->toBe(400);

    $response = $response->withStatus(600);

    expect($response->status())->toBe(503);
});

test('response should be returns correctly', function (): void {
    $errors = Mockery::mock(ErrorProvider::class, ['resolve' => [['message' => 'Something an error.']]]);
    $meta = Mockery::mock(MetaThrowable::class, ['resolve' => ['timestamp' => 'now']]);

    $response = new FailureResponse($errors, meta: $meta);

    expect($response->response())->toBeInstanceOf(JsonResponse::class);
});

<?php

declare(strict_types=1);

namespace Tests;

use Cndrsdrmn\LaravelFailures\Failure;
use Cndrsdrmn\LaravelFailures\FailureRenderer;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Validation\Rules\ImageFile;
use Illuminate\Validation\ValidationException;
use Mockery;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use UnexpectedValueException;

beforeEach(function (): void {
    Failure::shouldForceRender(false);
});

describe('with mock pipeline', function (): void {
    beforeEach(function (): void {
        $pipeline = Mockery::mock(Pipeline::class);
        $pipeline->shouldReceive('send')->once()->andReturnSelf();
        $pipeline->shouldReceive('through')->once()->andReturnSelf();
        $pipeline->shouldReceive('then')->once()->andReturns(new Response);

        $this->pipeline = $pipeline;
    });

    test('renders response when request expects JSON', function (): void {
        $request = Mockery::mock(Request::class, ['expectsJson' => true]);

        $renderer = new FailureRenderer($this->pipeline);
        $response = $renderer(new Exception, $request);

        expect($response)->toBeInstanceOf(Response::class);
    });

    test('force render overrides request expectation', function (): void {
        Failure::shouldForceRender();

        $request = Mockery::mock(Request::class);

        $renderer = new FailureRenderer($this->pipeline);
        $response = $renderer(new Exception, $request);

        expect($response)->toBeInstanceOf(Response::class);
    });
});

test('does not render response when request doest not expect JSON', function (): void {
    $request = Mockery::mock(Request::class, ['expectsJson' => false]);

    $response = app(FailureRenderer::class)(new Exception, $request);

    expect($response)->toBeNull();
});

test('finalize unhandled failure returns response', function (): void {
    $callback = (fn () => $this->finalizeUnhandledFailure(new Exception));
    $response = $callback->call(app(FailureRenderer::class));

    expect($response)->toBeInstanceOf(Response::class);
});

it('render finalize unhandled failure with debug true', function (): void {
    config(['app.debug' => true]);

    $request = Mockery::mock(Request::class, ['expectsJson' => true]);

    $renderer = app(FailureRenderer::class);
    $response = $this->createTestResponse(
        $renderer(new UnexpectedValueException, $request)
    );

    $response
        ->assertInternalServerError()
        ->assertJsonStructure([
            'message',
            'errors' => ['*' => ['attribute', 'message', 'prefix', 'violated']],
            'meta' => [
                'timestamp', 'trace_id', 'exception',
                'most_relevant_stacktrace' => $frame = ['function', 'file', 'line', 'in_app'],
                'stacktrace' => ['*' => $frame],
            ],
        ])
        ->assertJson([
            'message' => 'Whoops, looks like something went wrong.',
            'errors' => [['message' => 'An unexpected error occurred.']],
            'meta' => ['exception' => 'UnexpectedValueException'],
        ]);
});

it('render authentication exception', function (): void {
    $request = Mockery::mock(Request::class, ['expectsJson' => true]);

    $renderer = app(FailureRenderer::class);
    $response = $this->createTestResponse(
        $renderer(new AuthenticationException('Unauthenticated.'), $request)
    );

    $response
        ->dump()
        ->assertUnauthorized()
        ->assertJsonStructure([
            'message',
            'errors' => ['*' => ['attribute', 'message', 'prefix', 'violated']],
            'meta' => ['timestamp', 'trace_id'],
        ])
        ->assertJson([
            'message' => 'Unauthenticated.',
            'errors' => [
                [
                    'message' => 'Missing or invalid authentication token.',
                    'attribute' => 'Authorization',
                    'prefix' => 'header',
                    'violated' => 'authentication',
                ],
            ],
        ]);
});

it('render validation exception', function (): void {
    $request = Mockery::mock(Request::class, ['expectsJson' => true]);

    $validator = validator([
        'foo' => '',
        'bar' => 'file',
    ], [
        'foo' => 'required',
        'bar' => ImageFile::default(),
    ]);

    $renderer = app(FailureRenderer::class);
    $response = $this->createTestResponse(
        $renderer(new ValidationException($validator), $request)
    );

    $response
        ->assertUnprocessable()
        ->assertJson(fn (AssertableJson $json): AssertableJson => $json
            ->has('message')
            ->has('meta')
            ->has('errors', 2)
        )
        ->assertJson([
            'errors' => [
                [
                    'message' => 'The foo field is required.',
                    'attribute' => 'foo',
                    'prefix' => '/foo',
                    'violated' => 'required',
                ],
                [
                    'message' => 'The bar field must be a file.',
                    'attribute' => 'bar',
                    'prefix' => '/bar',
                    'violated' => 'file',
                ],
            ],
        ]);
});

it('render http exception', function (): void {
    $request = Mockery::mock(Request::class, ['expectsJson' => true]);

    $exception = HttpException::fromStatusCode(404, previous: new ModelNotFoundException);

    $renderer = app(FailureRenderer::class);
    $response = $this->createTestResponse($renderer($exception, $request));

    $response
        ->assertNotFound()
        ->assertJson([
            'errors' => [
                [
                    'message' => 'The requested resource could not be found.',
                    'prefix' => 'resource',
                    'violated' => 'request',
                    'attribute' => 'internal',
                ],
            ],
        ]);
});

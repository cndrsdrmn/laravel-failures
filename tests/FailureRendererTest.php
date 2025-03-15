<?php

declare(strict_types=1);

namespace Tests;

use Cndrsdrmn\LaravelFailures\Failure;
use Cndrsdrmn\LaravelFailures\FailureRenderer;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Mockery;
use Symfony\Component\HttpFoundation\Response;

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

<?php

declare(strict_types=1);

namespace Cndrsdrmn\LaravelFailures;

use Cndrsdrmn\LaravelFailures\Types\Breakdown;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * @internal
 */
final class FailureRenderer
{
    /**
     * An array of pipes to handle different types of exceptions.
     *
     * @var class-string[]
     */
    private array $pipes = [
        Pipes\AuthenticationExceptionRenderer::class,
        Pipes\HttpExceptionRenderer::class,
        Pipes\ValidationExceptionRenderer::class,
    ];

    /**
     * Create a new failure renderer instance.
     */
    public function __construct(private readonly Pipeline $pipeline)
    {
        //
    }

    /**
     * Finalizes the response for unhandled exceptions.
     */
    private function finalizeUnhandledFailure(Throwable $exception): Response
    {
        return Breakdown::throwable($exception)
            ->withStatus(Response::HTTP_INTERNAL_SERVER_ERROR)
            ->response();
    }

    /**
     * Determines if the exception should not be rendered based on the request.
     */
    private function shouldntRender(Request $request): bool
    {
        if (Failure::isForceRender()) {
            return false;
        }

        return ! $request->expectsJson();
    }

    /**
     * Invokes the failure renderer.
     */
    public function __invoke(Throwable $exception, Request $request): ?Response
    {
        if ($this->shouldntRender($request)) {
            return null;
        }

        return $this->pipeline // @phpstan-ignore return.type
            ->send($exception)
            ->through($this->pipes)
            ->then(fn (Throwable $exception): Response => $this->finalizeUnhandledFailure($exception));
    }
}

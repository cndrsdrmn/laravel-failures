<?php

declare(strict_types=1);

namespace Cndrsdrmn\LaravelFailures\Pipes;

use Closure;
use Cndrsdrmn\LaravelFailures\Types\Breakdown;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * @internal
 */
final class ValidationExceptionRenderer
{
    /**
     * Handle the given exception.
     *
     * @param  Closure(Throwable): Response  $next
     */
    public function handle(Throwable $exception, Closure $next): Response
    {
        if ($exception instanceof ValidationException) {
            return Breakdown::validation($exception)
                ->withStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
                ->response();
        }

        return $next($exception);
    }
}

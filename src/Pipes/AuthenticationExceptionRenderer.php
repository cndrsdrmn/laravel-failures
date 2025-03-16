<?php

declare(strict_types=1);

namespace Cndrsdrmn\LaravelFailures\Pipes;

use Closure;
use Cndrsdrmn\LaravelFailures\Types\Breakdown;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * @internal
 */
final class AuthenticationExceptionRenderer
{
    /**
     * Handle the given exception.
     *
     * @param  Closure(Throwable): Response  $next
     */
    public function handle(Throwable $exception, Closure $next): Response
    {
        if ($exception instanceof AuthenticationException) {
            return Breakdown::auth($exception)
                ->withStatus(Response::HTTP_UNAUTHORIZED)
                ->response();
        }

        return $next($exception);
    }
}

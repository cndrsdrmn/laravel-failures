<?php

declare(strict_types=1);

namespace Cndrsdrmn\LaravelFailures\Pipes;

use Closure;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * @internal
 *
 * @codeCoverageIgnore
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
        return $next($exception);
    }
}

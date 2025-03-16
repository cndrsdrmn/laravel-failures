<?php

declare(strict_types=1);

namespace Cndrsdrmn\LaravelFailures\Pipes;

use Closure;
use Cndrsdrmn\LaravelFailures\Types\Breakdown;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * @internal
 */
final class HttpExceptionRenderer
{
    /**
     * Handle the given exception.
     *
     * @param  Closure(Throwable): Response  $next
     */
    public function handle(Throwable $exception, Closure $next): Response
    {
        if ($exception instanceof HttpExceptionInterface) {
            return Breakdown::http($exception)->response();
        }

        return $next($exception);
    }
}

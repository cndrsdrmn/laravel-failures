<?php

declare(strict_types=1);

namespace Cndrsdrmn\LaravelFailures\Contracts;

/**
 * @template TKey of array-key
 * @template TValue
 */
interface Resolvable
{
    /**
     * Resolve the frame with an optional request.
     *
     * @param  \Illuminate\Http\Request|null  $request
     * @return array<TKey, TValue>
     */
    public function resolve($request = null): array;
}

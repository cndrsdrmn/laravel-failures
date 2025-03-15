<?php

declare(strict_types=1);

namespace Cndrsdrmn\LaravelFailures\Contracts;

use Illuminate\Contracts\Support\Arrayable;
use Throwable;

/**
 * @template-extends Arrayable<string, mixed>
 * @template-extends Resolvable<string, mixed>
 */
interface MetaThrowable extends Arrayable, Resolvable
{
    /**
     * Get the stacktrace associated with the exception.
     */
    public function stacktrace(): ?TraceableStack;

    /**
     * Get the exception associated with the meta instance.
     */
    public function throwable(): ?Throwable;

    /**
     * Get the timestamp when the meta instance was created.
     */
    public function timestamp(): string;

    /**
     * Get the tracer identifier.
     */
    public function tracer(): int|string;
}

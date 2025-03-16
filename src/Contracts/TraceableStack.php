<?php

declare(strict_types=1);

namespace Cndrsdrmn\LaravelFailures\Contracts;

use Cndrsdrmn\LaravelFailures\Types\Frame;
use Cndrsdrmn\LaravelFailures\Types\Stacktrace;
use Illuminate\Contracts\Support\Arrayable;
use Throwable;

/**
 * @template-extends Arrayable<int, array<string, mixed>>
 * @template-extends Resolvable<int, array<string, mixed>>
 */
interface TraceableStack extends Arrayable, Resolvable
{
    /**
     * Create a traceable stack instance from a throwable instance.
     */
    public static function createFromThrowable(Throwable $exception): self;

    /**
     * Get all frames from the stacktrace.
     *
     * @return TraceableFrame[]
     */
    public function all(): array;

    /**
     * Get the first relevant frame from the stacktrace.
     */
    public function firstRelevantFrame(): TraceableFrame;
}

<?php

declare(strict_types=1);

namespace Cndrsdrmn\LaravelFailures\Types;

use Cndrsdrmn\LaravelFailures\Contracts\MetaThrowable;
use Cndrsdrmn\LaravelFailures\Contracts\TraceableStack;
use Cndrsdrmn\LaravelFailures\Failure;
use Throwable;

/**
 * @internal
 */
final readonly class Meta implements MetaThrowable
{
    /**
     * The stacktrace associated with the exception.
     */
    private ?TraceableStack $stacktrace;

    /**
     * The timestamp when the meta instance was created.
     */
    private string $timestamp;

    /**
     * The tracer identifier, which can be an integer or a string.
     */
    private int|string $tracer;

    /**
     * Create a new meta instance.
     */
    public function __construct(private ?Throwable $exception = null)
    {
        $this->stacktrace = $this->prepareStacktrace();
        $this->timestamp = Failure::timestamp();
        $this->tracer = Failure::tracer();
    }

    /**
     * Resolve the frame with an optional request.
     *
     * @param  \Illuminate\Http\Request|null  $request
     * @return array<string, mixed>
     */
    public function resolve($request = null): array
    {
        return array_filter([
            'timestamp' => $this->timestamp,
            'trace_id' => $this->tracer,
            'exception' => $this->shouldRenderStacktrace() ? class_basename($this->exception) : null, // @phpstan-ignore argument.type
            'most_relevant_stacktrace' => $this->stacktrace?->firstRelevantFrame()->resolve($request),
            'stacktrace' => $this->stacktrace?->resolve($request),
        ]);
    }

    /**
     * Get the stacktrace associated with the exception.
     */
    public function stacktrace(): ?TraceableStack
    {
        return $this->stacktrace;
    }

    /**
     * Get the exception associated with the meta instance.
     */
    public function throwable(): ?Throwable
    {
        return $this->exception;
    }

    /**
     * Get the timestamp when the meta instance was created.
     */
    public function timestamp(): string
    {
        return $this->timestamp;
    }

    /**
     * Get the instance as an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->resolve();
    }

    /**
     * Get the tracer identifier.
     */
    public function tracer(): int|string
    {
        return $this->tracer;
    }

    /**
     * Generate a stacktrace from the exception if available.
     */
    private function prepareStacktrace(): ?TraceableStack
    {
        if ($this->shouldntRenderStacktrace()) {
            return null;
        }

        return Stacktrace::createFromThrowable($this->exception); // @phpstan-ignore argument.type
    }

    /**
     * Determine if the stacktrace should be rendered.
     */
    private function shouldRenderStacktrace(): bool
    {
        return ! $this->shouldntRenderStacktrace();
    }

    /**
     * Determine if the stacktrace should not be rendered.
     */
    private function shouldntRenderStacktrace(): bool
    {
        if (! config()->boolean('app.debug')) {
            return true;
        }

        return is_null($this->exception);
    }
}

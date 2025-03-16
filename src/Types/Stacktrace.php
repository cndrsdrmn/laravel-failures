<?php

declare(strict_types=1);

namespace Cndrsdrmn\LaravelFailures\Types;

use Cndrsdrmn\LaravelFailures\Contracts\TraceableFrame;
use Cndrsdrmn\LaravelFailures\Contracts\TraceableStack;
use Illuminate\Support\Collection;
use Throwable;

/**
 * @internal
 */
final readonly class Stacktrace implements TraceableStack
{
    /**
     * Collection of traceable frames.
     *
     * @var Collection<int, TraceableFrame>
     */
    private Collection $frames;

    /**
     * Create a new stacktrace instance.
     *
     * @param  array<int, TraceableFrame>  $frames
     */
    public function __construct(array $frames)
    {
        $this->frames = Collection::make($frames)
            ->whenEmpty(fn () => throw_if(true, 'UnexpectedValueException', 'Stacktrace cannot be empty.'))
            ->ensure(TraceableFrame::class);
    }

    /**
     * Create a traceable stack instance from a throwable instance.
     */
    public static function createFromThrowable(Throwable $exception): self
    {
        return self::createFromBacktrace(
            $exception->getTrace(), $exception->getFile(), $exception->getLine()
        );
    }

    /**
     * Create a stacktrace instance from a backtrace.
     *
     * @param  list<array{file?: string, line?: int}>  $backtrace
     */
    public static function createFromBacktrace(array $backtrace, string $file, int $line): self
    {
        $frames = [];

        foreach ($backtrace as $item) {
            $frames[] = Frame::createFromBacktrace($item, $file, $line);

            $file = $item['file'] ?? 'internal';
            $line = $item['line'] ?? 0;
        }

        $frames[] = Frame::createFromBacktrace([], $file, $line);

        return new self($frames);
    }

    /**
     * Get all frames from the stacktrace.
     *
     * @return TraceableFrame[]
     */
    public function all(): array
    {
        return $this->frames->all();
    }

    /**
     * Get the first relevant frame from the stacktrace.
     */
    public function firstRelevantFrame(): TraceableFrame
    {
        return $this->frames->first(fn (TraceableFrame $frame): bool => $frame->inApp(), $this->frames->first()); // @phpstan-ignore return.type
    }

    /**
     * Resolve the frame with an optional request.
     *
     * @param  \Illuminate\Http\Request|null  $request
     * @return array<int, array<string, mixed>>
     */
    public function resolve($request = null): array
    {
        return $this->frames->map(fn (TraceableFrame $frame): array => $frame->resolve($request))->all();
    }

    /**
     * Get the instance as an array.
     *
     * @return array<int, array<string, mixed>>
     */
    public function toArray()
    {
        return $this->frames->toArray(); // @phpstan-ignore return.type
    }
}

<?php

declare(strict_types=1);

namespace Cndrsdrmn\LaravelFailures\Types;

use Cndrsdrmn\LaravelFailures\Contracts\ErrorProvider;
use Cndrsdrmn\LaravelFailures\Contracts\IssueDescriptor;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use UnexpectedValueException;

/**
 * @internal
 */
final readonly class Breakdown implements ErrorProvider
{
    /**
     * Collection of error descriptors.
     *
     * @var Collection<int, IssueDescriptor>
     */
    private Collection $errors;

    /**
     * Create a new breakdown instance.
     *
     * @param  IssueDescriptor[]  $errors
     */
    public function __construct(array $errors = [])
    {
        $this->errors = Collection::make($errors)->ensure(IssueDescriptor::class);
    }

    /**
     * Wrap the given issue into a breakdown instance.
     *
     * @throws UnexpectedValueException
     */
    public static function wrap(mixed $issue): self
    {
        if ($issue instanceof self) {
            return $issue;
        }

        $issue = match (true) {
            $issue instanceof Enumerable => $issue->all(),
            $issue instanceof IssueDescriptor => Arr::wrap($issue),
            is_array($issue) => array_map(fn ($item): mixed => $item instanceof self ? self::wrap($item->all()) : $item, $issue),
            default => $issue,
        };

        if (is_array($issue)) {
            return new self($issue); // @phpstan-ignore argument.type
        }

        throw new UnexpectedValueException(
            sprintf('Cannot wrap the issue of type: %s.', get_debug_type($issue))
        );
    }

    /**
     * Get all errors as an array.
     *
     * @return IssueDescriptor[]
     */
    public function all(): array
    {
        return $this->errors->all();
    }

    /**
     * Resolve the frame with an optional request.
     *
     * @param  \Illuminate\Http\Request|null  $request
     * @return array<int, array<string, mixed>>
     */
    public function resolve($request = null): array
    {
        return $this->errors->map(fn (IssueDescriptor $error): array => $error->resolve($request))->all();
    }

    /**
     * Get the instance as an array.
     *
     * @return array<int, array<string, mixed>>
     */
    public function toArray(): array
    {
        return $this->errors->toArray(); // @phpstan-ignore return.type
    }
}

<?php

declare(strict_types=1);

namespace Cndrsdrmn\LaravelFailures\Types;

use Cndrsdrmn\LaravelFailures\Contracts\IssueDescriptor;

/**
 * @internal
 */
final readonly class Violation implements IssueDescriptor
{
    /**
     * The attribute related to the issue.
     */
    private string $attribute;

    /**
     * The message related to the issue.
     */
    private string $message;

    /**
     * The prefix related to the issue.
     */
    private string $prefix;

    /**
     * The violated attribute of the issue.
     */
    private string $violated;

    /**
     * Create a new violation instance.
     */
    public function __construct(
        ?string $message = null,
        ?string $violated = null,
        ?string $attribute = null,
        ?string $prefix = null
    ) {
        $this->message = $message ?: 'An unexpected error occurred.';
        $this->attribute = $attribute ?: 'internal';
        $this->violated = $violated ?: 'system';
        $this->prefix = $prefix ?: 'unknown';
    }

    /**
     * Get the attribute of the issue.
     */
    public function attribute(): string
    {
        return $this->attribute;
    }

    /**
     * Get the message of the issue.
     */
    public function message(): string
    {
        return $this->message;
    }

    /**
     * Get the prefix of the issue.
     */
    public function prefix(): string
    {
        return $this->prefix;
    }

    /**
     * Resolve the frame with an optional request.
     *
     * @param  \Illuminate\Http\Request|null  $request
     * @return array<string, string>
     */
    public function resolve($request = null): array
    {
        return [
            'attribute' => $this->attribute,
            'message' => $this->message,
            'prefix' => $this->prefix,
            'violated' => $this->violated,
        ];
    }

    /**
     * Get the instance as an array.
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return $this->resolve();
    }

    /**
     * Get the violated attribute of the issue.
     */
    public function violated(): string
    {
        return $this->violated;
    }
}

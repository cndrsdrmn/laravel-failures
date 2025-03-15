<?php

declare(strict_types=1);

namespace Cndrsdrmn\LaravelFailures\Contracts;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @template-extends Arrayable<string, mixed>
 * @template-extends Resolvable<string, mixed>
 */
interface TraceableFrame extends Arrayable, Resolvable
{
    /**
     * Get the file name.
     */
    public function file(): string;

    /**
     * Get the function name.
     */
    public function func(): ?string;

    /**
     * Determine if the frame is part of the application code.
     */
    public function inApp(): bool;

    /**
     * Get the line number.
     */
    public function line(): int;
}

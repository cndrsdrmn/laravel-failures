<?php

declare(strict_types=1);

namespace Cndrsdrmn\LaravelFailures\Contracts;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @template-extends Arrayable<int, array<string, mixed>>
 * @template-extends Resolvable<int, array<string, mixed>>
 */
interface ErrorProvider extends Arrayable, Resolvable, Responsable
{
    /**
     * Get all errors as an array.
     *
     * @return IssueDescriptor[]
     */
    public function all(): array;
}

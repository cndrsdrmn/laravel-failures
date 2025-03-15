<?php

declare(strict_types=1);

namespace Cndrsdrmn\LaravelFailures\Contracts;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @template-extends Arrayable<string, string>
 * @template-extends Resolvable<string, string>
 */
interface IssueDescriptor extends Arrayable, Resolvable
{
    /**
     * Get the attribute of the issue.
     */
    public function attribute(): string;

    /**
     * Get the message of the issue.
     */
    public function message(): string;

    /**
     * Get the prefix of the issue.
     */
    public function prefix(): string;

    /**
     * Get the violated attribute of the issue.
     */
    public function violated(): string;
}

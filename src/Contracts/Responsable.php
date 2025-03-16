<?php

declare(strict_types=1);

namespace Cndrsdrmn\LaravelFailures\Contracts;

use Illuminate\Contracts\Support\Responsable as IlluminateResponsable;
use Throwable;

interface Responsable extends IlluminateResponsable
{
    /**
     * Get the message for the response.
     */
    public function message(): string;

    /**
     * Get the HTTP status code for the response.
     */
    public function status(): int;

    /**
     * Handle the incoming request and return a response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function response($request = null);

    /**
     * Set the message for the response.
     */
    public function withMessage(string $message): self;

    /**
     * Set the HTTP status code for the response.
     */
    public function withStatus(int $status): self;

    /**
     * Set the throwable exception instance.
     */
    public function withThrowable(Throwable $exception): self;
}

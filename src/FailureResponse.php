<?php

declare(strict_types=1);

namespace Cndrsdrmn\LaravelFailures;

use Illuminate\Http\JsonResponse;
use Throwable;

/**
 * @internal
 */
final readonly class FailureResponse implements Contracts\Responsable
{
    /**
     * The message for the failure response.
     */
    private string $message;

    /**
     * The meta information for the throwable instance.
     */
    private Contracts\MetaThrowable $meta;

    /**
     * Create a new failure response instance.
     */
    public function __construct(
        private Contracts\ErrorProvider $errors,
        ?string $message = null,
        private int $status = 500,
        ?Contracts\MetaThrowable $meta = null
    ) {
        $this->message = $message ?: 'Whoops, looks like something went wrong.';
        $this->meta = $meta ?? $this->metaThrowable();
    }

    /**
     * Get the message for the response.
     */
    public function message(): string
    {
        return $this->message;
    }

    /**
     * Handle the incoming request and return a response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JsonResponse
     */
    public function response($request = null)
    {
        return $this->toResponse(
            $request ?? app()->make('request')
        );
    }

    /**
     * Get the HTTP status code for the response with ensures the status code is between 400 and 503.
     */
    public function status(): int
    {
        return max(400, min(503, $this->status));
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JsonResponse
     */
    public function toResponse($request)
    {
        return tap(new JsonResponse(
            $this->wrap(
                $this->errors->resolve($request),
                $this->meta->resolve($request),
            ),
            $this->status(),
        ), function (JsonResponse $response): void {
            $response->original = $this->errors;
        });
    }

    /**
     * Set the message for the response.
     */
    public function withMessage(string $message): self
    {
        return new self($this->errors, $message, $this->status, $this->meta);
    }

    /**
     * Set the HTTP status code for the response.
     */
    public function withStatus(int $status): self
    {
        return new self($this->errors, $this->message, $status, $this->meta);
    }

    /**
     * Set the throwable exception instance.
     */
    public function withThrowable(Throwable $exception): self
    {
        return new self($this->errors, $this->message, $this->status, $this->metaThrowable($exception));
    }

    /**
     * Create a new MetaThrowable instance.
     */
    private function metaThrowable(?Throwable $exception = null): Contracts\MetaThrowable
    {
        return new Types\Meta($exception);
    }

    /**
     * Wrap the failures and meta information into a response array.
     *
     * @param  array<int, array<string, mixed>>  $errors
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    private function wrap(array $errors, array $meta): array
    {
        return [
            'message' => $this->message,
            Failure::wrapper() => $errors,
            Failure::metaWrapper() => $meta,
        ];
    }
}

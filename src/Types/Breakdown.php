<?php

declare(strict_types=1);

namespace Cndrsdrmn\LaravelFailures\Types;

use Cndrsdrmn\LaravelFailures\Contracts\ErrorProvider;
use Cndrsdrmn\LaravelFailures\Contracts\IssueDescriptor;
use Cndrsdrmn\LaravelFailures\FailureResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Routing\Exceptions\BackedEnumCaseNotFoundException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;
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
     * The response object for handling failure responses.
     */
    private FailureResponse $response;

    /**
     * Create a new breakdown instance.
     *
     * @param  IssueDescriptor[]  $errors
     */
    public function __construct(array $errors = [], ?FailureResponse $response = null)
    {
        $this->errors = Collection::make($errors)->ensure(IssueDescriptor::class);
        $this->response = $response ?? new FailureResponse($this);
    }

    /**
     * Create a breakdown instance for an authentication exception.
     */
    public static function auth(AuthenticationException $exception): self
    {
        $issue = new Violation('Missing or invalid authentication token.', 'authentication', 'Authorization', 'header');

        return self::wrap($issue)->withMessage($exception->getMessage());
    }

    /**
     * Create a breakdown instance from a http exception.
     */
    public static function http(HttpExceptionInterface $exception): self
    {
        $previous = $exception->getPrevious() ?? $exception;

        $issue = new Violation(match (true) {
            $previous instanceof BackedEnumCaseNotFoundException,
            $previous instanceof ModelNotFoundException => 'The requested resource could not be found.',
            $previous instanceof TokenMismatchException => 'The provided token is invalid or has expired.',
            default => $previous->getMessage(),
        }, 'request', 'internal', 'resource');

        $status = $exception->getStatusCode() ?: Response::HTTP_BAD_REQUEST;

        return self::wrap($issue)
            ->withMessage(Response::$statusTexts[$status] ?? '') // @phpstan-ignore argument.type
            ->withStatus($status);
    }

    /**
     * Create a breakdown instance from a throwable.
     */
    public static function throwable(Throwable $exception): self
    {
        $issue = new Violation($exception->getMessage());

        return self::wrap($issue)->withThrowable($exception);
    }

    /**
     * Create a breakdown from a validation exception.
     */
    public static function validation(ValidationException $exception): self
    {
        /** @var array<string, array<string, list<string>>> $failed */
        $failed = $exception->validator->failed();
        $violations = Collection::make($failed)
            ->map(fn (array $parameters): string => Collection::make($parameters)
                ->map(function (array $parameter, string $rule): string {
                    $rule = class_exists($rule)
                        ? Str::of($rule)->classBasename()->snake()
                        : Str::of($rule)->snake();

                    return filled($parameter) ? "{$rule}:".implode(',', $parameter) : (string) $rule;
                })
                ->join('|')
            );

        /** @var array<string, list<string>> $errors */
        $errors = $exception->errors();
        $issues = Collection::make($errors)
            ->map(fn (array $messages, string $attribute): Violation => new Violation(
                message: $messages[0] ?? '',
                violated: $violations->get($attribute, ''),
                attribute: $attribute,
                prefix: Str::of($attribute)->start('/')
                    ->replace('.', '/')
                    ->value()
            ));

        return self::wrap($issues->values())->withMessage($exception->getMessage());
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
     * Get the message for the response.
     */
    public function message(): string
    {
        return $this->response->message();
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
     * Handle the incoming request and return a response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function response($request = null)
    {
        return $this->toResponse(
            $request ?? app()->make('request')
        );
    }

    /**
     * Get the HTTP status code for the response.
     */
    public function status(): int
    {
        return $this->response->status();
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

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toResponse($request)
    {
        return $this->response->toResponse($request);
    }

    /**
     * Set the message for the response.
     */
    public function withMessage(string $message): self
    {
        return new self($this->errors->all(), $this->response->withMessage($message));
    }

    /**
     * Set the HTTP status code for the response.
     */
    public function withStatus(int $status): self
    {
        return new self($this->errors->all(), $this->response->withStatus($status));
    }

    /**
     * Set the throwable exception instance.
     */
    public function withThrowable(Throwable $exception): self
    {
        return new self($this->errors->all(), $this->response->withThrowable($exception));
    }
}

<?php

declare(strict_types=1);

namespace Cndrsdrmn\LaravelFailures;

use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Support\Str;

final class Failure
{
    /**
     * The version of the Laravel Failures.
     */
    public const VERSION = '1.1.0';

    /**
     * Indicates whether to force the rendering of exceptions.
     */
    private static bool $forceRender = false;

    /**
     * The wrapper key for the meta information.
     */
    private static string $metaWrap = 'meta';

    /**
     * A callback function for generating timestamps.
     *
     * @var ?callable
     */
    private static $timestampCallback;

    /**
     * A callback function for generating trace IDs.
     *
     * @var ?callable
     */
    private static $tracerCallback;

    /**
     * The wrapper key for the error messages.
     */
    private static string $wrap = 'errors';

    /**
     * Resets the timestamp callback to its default behavior.
     */
    public static function createTimestampNormally(): void
    {
        self::$timestampCallback = null;
    }

    /**
     * Sets a custom callback function for generating timestamps.
     */
    public static function createTimestampUsing(callable $callback): void
    {
        self::$timestampCallback = $callback;
    }

    /**
     * Resets the tracer callback to its default behavior.
     */
    public static function createTracerNormally(): void
    {
        self::$tracerCallback = null;
    }

    /**
     * Sets a custom callback function for generating trace IDs.
     */
    public static function createTracerUsing(callable $callback): void
    {
        self::$tracerCallback = $callback;
    }

    /**
     * Registers a custom renderer for exceptions.
     */
    public static function handles(Exceptions $exceptions): void
    {
        $exceptions->renderable(self::renderer());
    }

    /**
     * Determine if the rendering of exceptions is forced.
     */
    public static function isForceRender(): bool
    {
        return self::$forceRender;
    }

    /**
     * Returns the wrapper key for the meta information.
     */
    public static function metaWrapper(): string
    {
        return self::$metaWrap;
    }

    /**
     * Returns an instance of the FailureRenderer.
     */
    public static function renderer(): FailureRenderer
    {
        return app(FailureRenderer::class);
    }

    /**
     * Forces the rendering of exceptions.
     */
    public static function shouldForceRender(bool $enabled = true): void
    {
        self::$forceRender = $enabled;
    }

    /**
     * Generates a timestamp using the custom timestamp callback if set, otherwise uses the current time.
     */
    public static function timestamp(): string
    {
        $timestamp = now()->toISOString();

        if (is_callable(self::$timestampCallback)) {
            $timestamp = call_user_func(self::$timestampCallback);

            assert(is_string($timestamp) || is_numeric($timestamp), 'The timestamp type should be string or numeric.');
        }

        return (string) $timestamp;
    }

    /**
     * Generates a trace ID using the custom tracer callback if set, otherwise generates a UUID.
     */
    public static function tracer(): int|string
    {
        if (is_callable(self::$tracerCallback)) {
            $tracer = call_user_func(self::$tracerCallback);

            assert(is_int($tracer) || is_string($tracer), 'The tracer type should be int or string.');

            return $tracer;
        }

        return str_replace('-', '', (string) Str::orderedUuid());
    }

    /**
     * Sets the wrapper key for the meta information.
     */
    public static function wrapMetaUsing(string $value): void
    {
        self::$metaWrap = $value;
    }

    /**
     * Sets the wrapper key for the error messages.
     */
    public static function wrapUsing(string $value): void
    {
        self::$wrap = $value;
    }

    /**
     * Returns the wrapper key for the error messages.
     */
    public static function wrapper(): string
    {
        return self::$wrap;
    }
}

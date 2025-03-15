<?php

declare(strict_types=1);

namespace Cndrsdrmn\LaravelFailures;

final class Failure
{
    /**
     * Indicates whether to force the rendering of exceptions.
     */
    private static bool $forceRender = false;

    /**
     * The wrapper key for the meta information.
     */
    private static string $metaWrap = 'meta';

    /**
     * The wrapper key for the error messages.
     */
    private static string $wrap = 'errors';

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
     * Forces the rendering of exceptions.
     */
    public static function shouldForceRender(bool $enabled = true): void
    {
        self::$forceRender = $enabled;
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

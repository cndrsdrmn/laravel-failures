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
     * Determine if the rendering of exceptions is forced.
     */
    public static function isForceRender(): bool
    {
        return self::$forceRender;
    }

    /**
     * Forces the rendering of exceptions.
     */
    public static function shouldForceRender(bool $enabled = true): void
    {
        self::$forceRender = $enabled;
    }
}

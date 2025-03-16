<?php

declare(strict_types=1);

namespace Cndrsdrmn\LaravelFailures\Types;

use Cndrsdrmn\LaravelFailures\Contracts\TraceableFrame;

/**
 * @internal
 */
final readonly class Frame implements TraceableFrame
{
    /**
     * Create a new frame instance.
     */
    public function __construct(
        private ?string $function,
        private string $file,
        private int $line,
        private bool $inApp
    ) {}

    /**
     * Create a new frame instance from backtrace.
     *
     * @param  array{function?: string, class?: string}  $backtrace
     */
    public static function createFromBacktrace(array $backtrace, string $file, int $line): self
    {
        if (preg_match('/^(.*)\((\d+)\) : (?:eval\(\)\'d code|runtime-created function)$/', $file, $matches)) {
            $file = $matches[1];
            $line = (int) $matches[2];
        }

        $function = $backtrace['function'] ?? null;

        if (isset($backtrace['class'])) {
            $class = preg_replace('/(?::\d+\$|0x)[a-fA-F0-9]+$/', '', $backtrace['class']);
            $function = "{$class}::{$function}";
        }

        return new self($function, $file, $line, self::isFrameInApp($file));
    }

    /**
     * Get the file name.
     */
    public function file(): string
    {
        return $this->file;
    }

    /**
     * Get the function name.
     */
    public function func(): ?string
    {
        return $this->function;
    }

    /**
     * Determine if the frame is part of the application code.
     */
    public function inApp(): bool
    {
        return $this->inApp;
    }

    /**
     * Get the line number.
     */
    public function line(): int
    {
        return $this->line;
    }

    /**
     * Resolve the frame with an optional request.
     *
     * @param  \Illuminate\Http\Request|null  $request
     * @return array<string, mixed>
     */
    public function resolve($request = null): array
    {
        return [
            'function' => $this->function,
            'file' => $this->file,
            'line' => $this->line,
            'in_app' => $this->inApp,
        ];
    }

    /**
     * Convert the frame instance to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->resolve();
    }

    /**
     * Determine if the frame is part of the application code.
     */
    private static function isFrameInApp(string $file): bool
    {
        if ($file === 'internal') {
            return false;
        }

        $isInApp = true;

        foreach ([base_path('vendor'), base_path('artisan')] as $appPath) {
            if (mb_substr($file, 0, mb_strlen($appPath)) === $appPath) {
                $isInApp = false;
                break;
            }
        }

        return $isInApp;
    }
}

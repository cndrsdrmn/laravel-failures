<?php

declare(strict_types=1);

namespace Tests\Types;

use Cndrsdrmn\LaravelFailures\Types\Frame;

beforeEach(function (): void {
    $this->expectedFrame = [
        'function' => 'funcOne',
        'file' => 'FileOne.php',
        'line' => 20,
        'inApp' => true,
    ];
});

dataset('frame cases', [
    'function' => [
        ['function' => 'backtrace'],
        'Frame.php',
        10,
        ['function' => 'backtrace', 'file' => 'Frame.php', 'line' => 10, 'in_app' => true],
    ],
    'class' => [
        ['function' => 'backtrace', 'class' => 'Frame'],
        'Frame.php',
        10,
        ['function' => 'Frame::backtrace', 'file' => 'Frame.php', 'line' => 10, 'in_app' => true],
    ],
    'eval file' => [
        ['function' => 'backtrace', 'class' => 'Frame'],
        'Frame.php(25) : eval()\'d code',
        10,
        ['function' => 'Frame::backtrace', 'file' => 'Frame.php', 'line' => 25, 'in_app' => true],
    ],
    'internal' => [
        ['function' => 'backtrace', 'class' => 'Frame'],
        'internal',
        10,
        fn (): array => ['function' => 'Frame::backtrace', 'file' => 'internal', 'line' => 10, 'in_app' => false],
    ],
    'vendor' => [
        ['function' => 'backtrace', 'class' => 'Frame'],
        fn () => base_path('vendor/package/Frame.php'),
        10,
        fn (): array => ['function' => 'Frame::backtrace', 'file' => base_path('vendor/package/Frame.php'), 'line' => 10, 'in_app' => false],
    ],
]);

test('create from backtrace', function (array $backtrace, string $file, int $line, array $expected): void {
    $frame = Frame::createFromBacktrace($backtrace, $file, $line);

    expect($frame)
        ->toBeInstanceOf(Frame::class)
        ->func()->toBe($expected['function'])
        ->file()->toBe($expected['file'])
        ->line()->toBe($expected['line'])
        ->inApp()->toBe($expected['in_app']);
})->with('frame cases');

test('resolve should be returns an array of frame', function (): void {
    $frame = new Frame(...$this->expectedFrame);
    unset($this->expectedFrame['inApp']);

    expect($frame->resolve())->toEqualCanonicalizing([
        ...$this->expectedFrame,
        'in_app' => true,
    ]);
});

test('returns an array of frame', function (): void {
    $frame = new Frame(...$this->expectedFrame);
    unset($this->expectedFrame['inApp']);

    expect($frame->toArray())->toEqualCanonicalizing([
        ...$this->expectedFrame,
        'in_app' => true,
    ]);
});

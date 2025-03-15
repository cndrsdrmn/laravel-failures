# Laravel Failures - Configuration Guide

This guide outlines how to configure **Laravel Failures**, allowing you to customize error handling and response formatting within your Laravel application.

## Exception Handling Integration

To ensure Laravel Failures properly handles exceptions, integrate it within your **bootstrap/app.php** file using one of the following approaches:

### Option 1: Using `Failure::handles()`
This method automatically registers the package as the default exception handler.

```php
use Cndrsdrmn\LaravelFailures\Failure;

->withExceptions(function (Exceptions $exceptions) {
    Failure::handles($exceptions);
})
```

### Option 2: Manually Register as a Renderable Handler
If you prefer to manually define exception handling behavior, use:

```php
use Cndrsdrmn\LaravelFailures\Failure;

->withExceptions(function (Exceptions $exceptions) {
    $exceptions->renderable(Failure::renderer());
})
```

## Force Rendering

By default, **force rendering** is disabled. To enable it, modify your `AppServiceProvider`:

```php
<?php

namespace App\Providers;

use Cndrsdrmn\LaravelFailures\Failure;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Failure::shouldForceRender();
    }
}
```

## Customizing Response Wrapping

By default, Laravel Failures wraps failures and metadata under default keys. You can customize these keys using the following methods:

```php
<?php

namespace App\Providers;

use Cndrsdrmn\LaravelFailures\Failure;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Failure::wrapUsing('failures'); // Default: 'errors'
        Failure::wrapMetaUsing('context'); // Default: 'meta'
    }
}
```

## Customizing Timestamp Format

The default timestamp uses `now()->toISOString()`. You can override it as follows:

```php
<?php

namespace App\Providers;

use Cndrsdrmn\LaravelFailures\Failure;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Failure::createTimestampUsing(fn () => now()->timestamp);
    }
}
```

## Customizing Tracer ID

The **tracer ID** is a unique identifier for tracking failures. By default, Laravel Failures generates an **ordered UUID** (`Str::orderedUuid()`) **without dashes (`-`)**. You can override this behavior.

```php
<?php

namespace App\Providers;

use Cndrsdrmn\LaravelFailures\Failure;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Failure::createTracerUsing(fn () => bin2hex(random_bytes(16))); // Generates a secure random ID
    }
}
```

---

By following this guide, you can fully configure Laravel Failures to suit your application's needs. 🚀


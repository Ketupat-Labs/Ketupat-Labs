<?php

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
*/

$app = new Illuminate\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

/*
|--------------------------------------------------------------------------
| Configuration
|--------------------------------------------------------------------------
*/

// Trust all proxies (Required for Render)
Illuminate\Http\Request::setTrustedProxies(
    ['*'],
    Illuminate\Http\Request::HEADER_X_FORWARDED_FOR |
    Illuminate\Http\Request::HEADER_X_FORWARDED_HOST |
    Illuminate\Http\Request::HEADER_X_FORWARDED_PORT |
    Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO |
    Illuminate\Http\Request::HEADER_X_FORWARDED_AWS_ELB
);

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
*/

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
*/

return $app;


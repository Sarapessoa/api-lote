<?php

use Laravel\Sanctum\Sanctum;

return [
    'stateful' => [],
    'guard' => [],
    'expiration' => 60,
    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),
    'middleware' => [],
];
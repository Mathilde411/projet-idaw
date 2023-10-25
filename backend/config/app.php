<?php
return [
    'prefix' => '/backend',
    'services' => [
        \App\Services\DatabaseServiceProvider::class,
        \App\Services\ValidationServiceProvider::class,
        \App\Services\HttpServiceProvider::class
    ]
];
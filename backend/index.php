<?php

use App\Http\Kernel;

$app = require __DIR__ . '/bootstrap/app.php';

$app->start();

$kernel = $app->make(Kernel::class);
$app->makeImmediateInjectedCall([$kernel, 'handle']);

$app->stop();
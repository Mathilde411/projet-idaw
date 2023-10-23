<?php

use App\Model\User;

$app = require __DIR__ . '/bootstrap/app.php';

echo $app->makeImmediateInjectedCall([\App\Test::class, 'test']);
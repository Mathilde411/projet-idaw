<?php

use App\Application;

require __DIR__ . '/autoload.php';

$app = new Application(
    dirname(__DIR__)
);

return $app;
<?php

spl_autoload_register(function ($class_name) {
    $prefix = "App";
    $replacer = __DIR__ . '/../app/';

    if (!str_starts_with($class_name, $prefix))
        return;

    $class_name = substr($class_name, 4);

    $filename = $replacer . $class_name . '.php';
    $filename = str_replace('\\', '/', $filename);
    include $filename;
});
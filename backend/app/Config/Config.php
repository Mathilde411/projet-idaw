<?php

namespace App\Config;

use App\Application;

class Config
{

    protected array $cache = [];

    public function __construct(protected Application $app)
    {}

    public function loadConfig(string $rootConfigName) : bool{
        $configFile = $this->app->getBasePath() . '/config/' . $rootConfigName . '.php';

        if(!file_exists($configFile))
            return false;

        $this->cache[$rootConfigName] = require $configFile;
        return true;
    }

    public function get(string $key, mixed $default = null) {
        $components = explode('.', $key);
        if(!isset($cache[$components[0]])) {
            if(!$this->loadConfig($components[0])) {
                return $default;
            }
        }

        $current = $this->cache;
        foreach ($components as $component) {
            if(!is_array($current) or !isset($current[$component]))
                return $default;

            $current = $current[$component];
        }

        return $current;
    }
}
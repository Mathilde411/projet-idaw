<?php

namespace App\Http;

use App\Application;

class Kernel
{
    public function __construct(protected Application $app)
    {}
}
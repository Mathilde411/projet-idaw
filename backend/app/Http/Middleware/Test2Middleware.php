<?php

namespace App\Http\Middleware;

use App\Http\Request;
use Closure;

class Test2Middleware
{
    public function handle(Request $request, Closure $next) {
        echo "Test2";
        return $next();
    }
}
<?php

namespace App\Http\Middleware;

use App\Http\Request;
use Closure;

class TestMiddleware
{
    public function handle(Request $request, Closure $next) {
        echo "Test1";
        return $next();
    }
}
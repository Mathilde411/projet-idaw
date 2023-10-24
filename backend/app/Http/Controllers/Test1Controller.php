<?php

namespace App\Http\Controllers;

use App\Http\Request;

class Test1Controller
{
    public function test1(Request $request) {
        echo "test1-1 " . $request->getMethod();
    }

    public function test2(Request $request, int $id) {
        echo "test1-2 " . $request->getMethod();
    }
}
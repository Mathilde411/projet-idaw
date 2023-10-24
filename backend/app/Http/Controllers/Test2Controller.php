<?php

namespace App\Http\Controllers;

use App\Http\Request;

class Test2Controller
{
    public function test1(Request $request) {
        echo "test2-1 " . $request->getMethod();
    }

    public function test2(Request $request) {
        echo "test2-2 " . $request->getMethod();
    }
}
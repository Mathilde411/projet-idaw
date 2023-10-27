<?php

namespace App\Http\Controllers;

use App\Facade\DB;
use App\Http\Request;
use App\Model\User;

class TestController
{
    public function test(Request $request) {
        $u = User::all();
        return $u;
    }
}
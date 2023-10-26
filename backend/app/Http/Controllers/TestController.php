<?php

namespace App\Http\Controllers;

use App\Database\QueryBuilder;
use App\Facade\DB;
use App\Http\Request;

class TestController
{
    public function test(Request $request) {
        return DB::table('users')->where('id', '=', 1)->first();
    }
}
<?php

namespace App\Http\Controllers;

use App\Database\QueryBuilder;
use App\Facade\DB;
use App\Http\Request;

class TestController
{
    public function test(Request $request) {
        return DB::table('users')
            ->where('a', '>', 'b')
            ->where(function (QueryBuilder $query) {
                return $query->where('date', '<', '4-11-2022')
                    ->orWhere('date', '>=', '4-11-2023');
            })
            ->get(['name', 'password']);
    }
}
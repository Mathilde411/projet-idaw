<?php

namespace App\Http\Controllers;

use App\Facade\DB;
use App\Http\Request;

class TestController
{
    public function test(Request $request) {
        return DB::table('consos')
            ->groupBy('id_users')
            ->select(['id_users', DB::raw('SUM(conso) AS conso_totale'), DB::raw('MAX(plus) as mplus')])
            ->orderBy([['conso_totale', true], ['id_users', false]])
            ->get();
    }
}
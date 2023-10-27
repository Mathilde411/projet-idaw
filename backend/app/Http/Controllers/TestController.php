<?php

namespace App\Http\Controllers;

use App\Facade\DB;
use App\Http\Request;
use App\Model\User;

class TestController
{
    public function test(Request $request) {
        return User::select(['users.id', DB::min('users.name', 'name'), DB::sum('consos.conso', 'total'), DB::avg('consos.conso', 'average')])
            ->join('consos', 'users.id', '=', 'consos.user_id')
            ->groupBy(['users.id'])
            ->orderBy('total')
            ->get();
    }
}
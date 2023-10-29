<?php

namespace App\Http\Controllers;

use App\Facade\DB;
use App\Http\Request;
use App\Model\Conso;
use App\Model\Repas;
use App\Model\User;

class TestController
{
    public function test(Request $request) {
        $user = User::find(3);
        return $user?->parents;
    }
}
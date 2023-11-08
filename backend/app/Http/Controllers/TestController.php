<?php

namespace App\Http\Controllers;

use App\Facade\DB;
use App\Http\Request;
use App\Model\Conso;
use App\Model\Repas;
use App\Model\User;

class TestController
{
    public function test(Request $request)
    {
        $jean = User::create([
            'name' => 'Jean',
            'email' => 'jean@jean.fr'
        ]);

        $jean->parents()->link(User::find(1), ['boo' => 'Mère']);
        $jean->parents()->link(User::find(3), ['boo' => 'Père']);
        return $jean->parents;
    }
}
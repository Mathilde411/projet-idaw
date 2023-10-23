<?php

namespace App;

use App\Database\DatabaseManager;
use App\Http\Request;

class Test
{
    public function __construct(protected DatabaseManager $man)
    {
    }

    public function test(Request $req)
    {
        var_dump($this->man);
        return $req->getMethod();
    }
}
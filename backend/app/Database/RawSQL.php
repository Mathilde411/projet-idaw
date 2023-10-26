<?php

namespace App\Database;

class RawSQL
{
    public function __construct(public string $raw)
    {
    }
}
<?php

namespace App\Database;

interface QueryProvider
{
    public function getQuery() : DbQueryBuilder;
}
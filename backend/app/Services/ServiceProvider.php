<?php

namespace App\Services;

use App\Application;

class ServiceProvider
{

    public function __construct(protected Application $app)
    {}

    public function register(){}
    public function up(){}
    public function down(){}
}
<?php

namespace App\Validation;

use Exception;

class ValidationError extends Exception
{
 public function __construct(string $message)
 {
     parent::__construct($message);
 }
}
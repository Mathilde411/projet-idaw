<?php

namespace App\Http\Error;

use App\Http\Error\HttpError;
use Throwable;

class ServerError extends HttpError
{
    public function __construct(string $message = "Erreur interne du serveur. ", string $title = "Internal Server Error", int $code = 500, ?Throwable $previous = null)
    {
        parent::__construct($message, $title, $code, $previous);
    }
}
<?php

namespace App\Http\Error;

class MethodNotAllowedError extends HttpError
{
    public function __construct(string $message = "Méthode de requête non autorisée.", string $title = "Method Not Allowed", int $code = 405, ?Throwable $previous = null)
    {
        parent::__construct($message, $title, $code, $previous);
    }
}
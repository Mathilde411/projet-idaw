<?php

namespace App\Http\Error;

class BadRequestError extends HttpError
{
    public function __construct(string $message = "La requête est malformée.", string $title = "Bad request", int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct($message, $title, $code, $previous);
    }
}
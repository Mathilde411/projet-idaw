<?php

namespace App\Http\Error;

use Throwable;

class NotFoundError extends HttpError
{
    public function __construct(string $message = "Ressource non trouvée.", string $title = "Not found", int $code = 404, ?Throwable $previous = null)
    {
        parent::__construct($message, $title, $code, $previous);
    }
}
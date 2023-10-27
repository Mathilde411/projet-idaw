<?php

namespace App\Http\Error;

use Throwable;

class HttpError extends \Exception
{
    protected string $title;

    public function __construct(string $message, string $title, int $code = 0, ?Throwable $previous = null)
    {
        $this->title = $title;
        parent::__construct($message, $code, $previous);
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }


}
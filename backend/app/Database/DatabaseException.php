<?php

namespace App\Database;

class DatabaseException extends \RuntimeException
{
    protected int|string $sqlCode;

    public function __construct(string $message = "", int|string $sqlCode = 0, int $code = 0, ?Throwable $previous = null)
    {
        $this->sqlCode = $sqlCode;
        parent::__construct($message, $code, $previous);
    }

    public function getSqlCode(): int|string
    {
        return $this->sqlCode;
    }

    public function setSqlCode(int|string $sqlCode): void
    {
        $this->sqlCode = $sqlCode;
    }



}
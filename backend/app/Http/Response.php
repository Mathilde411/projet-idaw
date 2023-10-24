<?php

namespace App\Http;

class Response
{
    protected string $body = '';
    protected int $code = 200;
    protected array $headers = [];

    public function content(mixed $content, int $code = 200, array $headers = []): static
    {
        if(!is_string($content)) {
            $content = json_encode($content);
            $this->header('Content-Type', 'application/json; charset=utf-8');
        }

        $this->body($content);
        $this->code($code);
        $this->headers($headers);

        return $this;
    }

    public function noContent(array $headers = []) {
        return $this->content('', 201, $headers);
    }

    public function body(string $body): static
    {
        $this->body = $body;
        return $this;
    }

    public function code(int $code): static
    {
        $this->code = $code;
        return $this;
    }

    public function header(string $key, string $value): static
    {
        $this->headers[$key] = $value;
        return $this;
    }

    public function headers(array $headers): static
    {
        $this->headers += $headers;
        return $this;
    }

    public function apply() {
        http_response_code($this->code);
        foreach ($this->headers as $key => $value) {
            header($key . ": " . $value);
        }
        echo $this->body;
    }


}
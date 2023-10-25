<?php

namespace App\Http;

class Request
{
    private string $method;
    private string $path;
    private ?string $query;
    private ?string $fragment;
    /**
     * @var string[]
     */
    private array $pathComponents;
    private array $queryParams;
    private string $body;
    private array $headers;
    /**
     * @var array|mixed
     */
    private array $bodyParams;
    private array $parameters;

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'];

        $urlInfo = parse_url($_SERVER['REQUEST_URI']);
        $this->path = $urlInfo['path'];

        if(isset($urlInfo['query']))
            $this->query = $urlInfo['query'];

        if(isset($urlInfo['fragment']))
            $this->fragment = $urlInfo['fragment'];

        $this->pathComponents = explode('/', trim($this->path, '/'));

        $this->queryParams = [];
        if(isset($this->query))
            parse_str($this->query, $this->queryParams);

        $this->body = file_get_contents('php://input');
        $this->headers = getallheaders();

        $this->bodyParams = [];
        if(($ct = $this->headers['Content-Type']) !== null) {
            if(str_starts_with($ct, "application/x-www-form-urlencoded")) {
                parse_str($this->body, $this->bodyParams);
            } elseif (str_starts_with($ct, "multipart/form-data")) {
                // TODO decode form-data
            } elseif (str_starts_with($ct, "application/json")) {
                $this->bodyParams = json_decode($this->body, true);
            }
        }

        if(in_array($this->method, ["PUT", "POST", "PATCH"])) {
            $this->parameters = $this->bodyParams;
        } else {
            $this->parameters = $this->queryParams;
        }
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getFragment(): string
    {
        return $this->fragment;
    }

    public function getPathComponents(): array
    {
        return $this->pathComponents;
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getBodyParams(): array
    {
        return $this->bodyParams;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }


}
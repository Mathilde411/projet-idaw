<?php

namespace App\Http;

use App\Application;
use App\Http\Error\HttpError;
use App\Http\Routing\Router;

class Kernel
{
    public function __construct(protected Application $app, protected Router $router)
    {}

    public function handle(Request $request, Response $response): void
    {
        try {
            $this->router->route($request);
        } catch (HttpError $e) {
            http_response_code($e->getCode());
            echo sprintf('
            <html lang="fr">
                <head>
                    <title>Erreur: %s</title>
                </head>
                <body>
                    <h1>%d: %s</h1>
                    <p>%s</p>
                </body>
            </html>
            ', $e->getTitle(), $e->getCode(), $e->getTitle(), $e->getMessage());
        }
    }
}
<?php

namespace App\Http;

use App\Application;
use App\Http\Error\HttpError;
use App\Http\Routing\Router;

class Kernel
{
    public function __construct(protected Application $app, protected Router $router)
    {
    }

    public function handle(Request $request): void
    {
        try {
            $response = $this->router->route($request);
            if (!($response instanceof Response))
                $response = \App\Facade\Response::content($response);
        } catch (HttpError $e) {
            $response = \App\Facade\Response::content(sprintf('
            <html lang="fr">
                <head>
                    <title>Erreur: %s</title>
                </head>
                <body>
                    <h1>%d: %s</h1>
                    <p>%s</p>
                </body>
            </html>
            ', $e->getTitle(), $e->getCode(), $e->getTitle(), $e->getMessage()), $e->getCode());
        }

        $response->apply();
    }
}
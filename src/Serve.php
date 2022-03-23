<?php declare(strict_types=1);

namespace App;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestinterface;
use Laminas\Diactoros\Response;

class Serve
{
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write('Hello World!');
        return $response->withStatus(200);
    }
}

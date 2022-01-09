<?php

namespace Mrkrash\Base\Http\RouteHandler;

use Laminas\Diactoros\Response\JsonResponse;
use Mrkrash\Base\Http\RouteHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Home implements RouteHandler
{
    public function __invoke(Request $request, array $args): Response
    {
        return new JsonResponse([
            'links' => [
                'Items' => $request->getUri() . 'Items',
                'docs' => $request->getUri() . 'docs',
            ],
        ]);
    }
}
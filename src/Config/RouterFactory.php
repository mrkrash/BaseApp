<?php declare(strict_types=1);

namespace Mrkrash\Base\Config;

use Assert\Assert;
use League\Route\Router as LeagueRouter;
use League\Route\Strategy\ApplicationStrategy;
use Middlewares\BasicAuthentication;
use Middlewares\ContentType;
use Middlewares\JsonPayload;
use Mrkrash\Base\Http\RouteHandler;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface as Container;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Server\MiddlewareInterface as Middleware;

class RouterFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(Container $container): LeagueRouter
    {
        $strategy = new ApplicationStrategy();
        $strategy->setContainer($container);
        $router = new LeagueRouter();
        $router->setStrategy($strategy);

        $authMiddleware = $container->get(BasicAuthentication::class);
        $contentNegotiationMiddleware = $container->get(ContentType::class);
        Assert::thatAll([$authMiddleware, $contentNegotiationMiddleware])->isInstanceOf(Middleware::class);

        $router->middleware($contentNegotiationMiddleware);
        $router->middleware(new JsonPayload());

        $router->map('GET', '/', RouteHandler\Home::class);

        return $router;
    }
}
<?php declare(strict_types=1);

namespace Mrkrash\Base;

use DI\DependencyException;
use DI\NotFoundException;
use JsonException;
use Laminas\Diactoros\Response\JsonResponse;
use League\Route\Http\Exception as HttpException;
use League\Route\Router;
use Mrkrash\Base\Model\InvalidDataException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;

class App implements ContainerInterface, RequestHandlerInterface
{
    public const CACHE_DIR = 'var/cache';

    private ContainerInterface $container;
    private Router $router;

    /**
     * @throws DependencyException
     * @throws NotFoundException
     * @throws JsonException
     */
    public static function bootstrap(): self
    {
        $container = (new Config\DependencyInjection())();
        $app = $container->get(__CLASS__);
        $app->container = $container;

        return $app;
    }

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @inheritDoc
     */
    public function handle(Request $request): Response
    {
        try {
            $response = $this->router->dispatch($request);
        } catch (InvalidDataException $exception) {
            return new JsonResponse(['error' => exception_to_array($exception)], 400);
        } catch (HttpException $exception) {
            return new JsonResponse(['error' => exception_to_array($exception)], $exception->getStatusCode());
        }

        return $response;
    }

    /**
     * @inheritDoc
     */
    public function get(string $id)
    {
        return $this->container->get($id);
    }

    /**
     * @inheritDoc
     */
    public function has(string $id)
    {
        return $this->container->has($id);
    }
}
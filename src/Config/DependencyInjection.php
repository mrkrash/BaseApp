<?php declare(strict_types=1);

namespace Mrkrash\Estimate\Config;

use DI\Container;
use DI\ContainerBuilder;
use Exception;
use JsonException;
use League\Route\Router;
use Middlewares\BasicAuthentication;
use Middlewares\ContentType;
use Mrkrash\Estimate\App;
use Psr\Log\LoggerInterface;
use RedBeanPHP\ToolBox;
use function DI\create;
use function DI\factory;
use function Env\env;

class DependencyInjection
{
    /**
     * @throws JsonException
     * @throws Exception
     */
    public function __invoke(): Container
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            BasicAuthentication::class => create()->constructor(
                json_decode(env('AUTH_USERS'), true, 512, JSON_THROW_ON_ERROR)
            ),
            ContentType::class => create()->constructor(['json'])->method("errorResponse"),
            ToolBox::class => factory(ToolBoxFactory::class),
            LoggerInterface::class => factory('\Mrkrash\Estimate\logger'),
            Router::class => factory(RouterFactory::class),
        ]);

        if (env('CACHE')) {
            $builder->enableCompilation(App::CACHE_DIR);
        }

        return $builder->build();
    }
}
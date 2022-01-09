<?php declare(strict_types=1);

namespace Mrkrash\Base\Config;

use DI\Container;
use DI\ContainerBuilder;
use Exception;
use JsonException;
use League\Route\Router;
use Middlewares\BasicAuthentication;
use Middlewares\ContentType;
use Mrkrash\Base\App;
use Mrkrash\Base\Model\ItemDataMapper;
use Psr\Log\LoggerInterface;
use RedBeanPHP\ToolBox;
use function DI\autowire;
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
            ItemDataMapper::class => autowire()->lazy(),
            LoggerInterface::class => factory('\Mrkrash\Base\logger'),
            Router::class => factory(RouterFactory::class),
            ToolBox::class => factory(ToolBoxFactory::class),
        ]);

        if (env('CACHE')) {
            $builder->enableCompilation(App::CACHE_DIR);
        }

        return $builder->build();
    }
}
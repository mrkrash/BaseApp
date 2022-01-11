<?php declare(strict_types=1);

namespace Mrkrash\Base\Http\RouteHandler;

use Doctrine\Instantiator\Exception\ExceptionInterface;
use Laminas\Diactoros\Response\EmptyResponse;
use League\Route\Http\Exception\BadRequestException;
use League\Route\Http\Exception\NotFoundException;
use Mrkrash\Base\Http\RouteHandler;
use Mrkrash\Base\Model\ItemDataMapper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ItemDelete implements RouteHandler
{
    private ItemDataMapper $itemDataMapper;

    public function __construct(ItemDataMapper $itemDataMapper)
    {
        $this->itemDataMapper = $itemDataMapper;
    }

    /**
     * @inheritDoc
     * @throws BadRequestException
     * @throws NotFoundException|ExceptionInterface
     */
    public function __invoke(Request $request, array $args): Response
    {
        if (!is_int($args['id'])) {
            throw new BadRequestException('Invalid ID');
        }

        $item = $this->itemDataMapper->findOne($args['id']);
        if ($item === null) {
            throw new NotFoundException('Resource Not Found');
        }

        $this->itemDataMapper->delete($item);

        return new EmptyResponse();
    }
}
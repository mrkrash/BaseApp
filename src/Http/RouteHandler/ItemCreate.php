<?php declare(strict_types=1);

namespace Mrkrash\Base\Http\RouteHandler;

use Doctrine\Instantiator\Exception\ExceptionInterface;
use Laminas\Diactoros\Response\JsonResponse;
use League\Route\Http\Exception\BadRequestException;
use Mrkrash\Base\Http\RouteHandler;
use Mrkrash\Base\Model\InvalidDataException;
use Mrkrash\Base\Model\Item;
use Mrkrash\Base\Model\ItemDataMapper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ItemCreate implements RouteHandler
{
    public ItemDataMapper $itemDataMapper;

    public function __construct(ItemDataMapper $itemDataMapper)
    {
        $this->itemDataMapper = $itemDataMapper;
    }

    /**
     * @throws BadRequestException
     * @throws ExceptionInterface
     * @throws InvalidDataException
     */
    public function __invoke(Request $request, array $args): Response
    {
        $requestBody = $request->getParsedBody();

        if (!is_array($requestBody)) {
            throw new BadRequestException('Invalid request body');
        }

        $item = Item::createFromArray($requestBody);
        $item_inserted = $this->itemDataMapper->insert($item);

        return new JsonResponse($item->withId($item_inserted->getId()), 201);
    }
}
<?php declare(strict_types=1);

namespace Mrkrash\Base\Http\RouteHandler;

use Doctrine\Instantiator\Exception\ExceptionInterface;
use Laminas\Diactoros\Response\JsonResponse;
use League\Route\Http\Exception\BadRequestException;
use League\Route\Http\Exception\NotFoundException;
use Mrkrash\Base\Model\InvalidDataException;
use Mrkrash\Base\Model\ItemDataMapper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ItemUpdate implements \Mrkrash\Base\Http\RouteHandler
{
    private ItemDataMapper $itemDataMapper;

    public function __construct(ItemDataMapper $itemDataMapper)
    {
        $this->itemDataMapper = $itemDataMapper;
    }

    /**
     * @inheritDoc
     * @throws BadRequestException
     * @throws NotFoundException
     * @throws InvalidDataException|ExceptionInterface
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

        $requestBody = $request->getParsedBody();
        if (!is_array($requestBody)) {
            throw new BadRequestException('Invalid Request Body');
        }

        $item->updateFromArray($requestBody);
        $this->itemDataMapper->update($item);

        return new JsonResponse($item, 200);
    }
}
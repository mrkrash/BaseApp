<?php declare(strict_types=1);

namespace Mrkrash\Base\Http\RouteHandler;

use Laminas\Diactoros\Response\JsonResponse;
use League\Route\Http\Exception\BadRequestException;
use Mrkrash\Base\Model\ItemDataMapper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ItemList implements \Mrkrash\Base\Http\RouteHandler
{
    private const MAX_PAGE_SIZE = 100;
    private ItemDataMapper $itemMapper;

    public function __construct(ItemDataMapper $itemMapper)
    {
        $this->itemMapper = $itemMapper;
    }

    /**
     * @inheritDoc
     * @throws BadRequestException
     */
    public function __invoke(Request $request, array $args): Response
    {
        $query = $request->getQueryParams();
        $search = $query['search'] ?? '';
        $page = (int) ($query['page'] ?? 1);
        if ($page < 1) {
            throw new BadRequestException('Page must be greater than 0');
        }
        $pageSize = (int) ($query['pageSize'] ?? ItemDataMapper::DEFAULT_PAGE_SIZE);

        if ($pageSize < 1 || $pageSize > self::MAX_PAGE_SIZE) {
            throw new BadRequestException(sprintf('Page size must be between 1 and %s', self::MAX_PAGE_SIZE));
        }

        $totalPages = $this->itemMapper->countPages($search, $pageSize);
        $items = $this->itemMapper->find($search, $page, $pageSize);

        $prev = $page > 1 ? min($page -1, $totalPages) : null;
        $next = $page < $totalPages ? $page + 1 : null;

        return new JsonResponse(compact('items', 'totalPages', 'prev', 'next'));
    }
}
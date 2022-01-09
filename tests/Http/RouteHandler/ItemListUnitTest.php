<?php declare(strict_types=1);

namespace Mrkrash\Base\Http\RouteHandler;

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;
use League\Route\Http\Exception\BadRequestException;
use Mrkrash\Base\Model\Item;
use Mrkrash\Base\Model\ItemDataMapper;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Mrkrash\Base\Http\RouteHandler\ItemList
 */
class ItemListUnitTest extends TestCase
{
    private ItemList $SUT;
    private ItemDataMapper $itemDataMapper;

    protected function setUp(): void
    {
        $this->itemDataMapper = $this->createMock(ItemDataMapper::class);
        $this->SUT = new ItemList($this->itemDataMapper);
    }

    /**
     * @throws BadRequestException
     */
    public function testSuccess(): void
    {
        $item1 = new Item('foo');
        $item2 = new Item('bar');
        $request = new ServerRequest();

        $records = [$item1, $item2];

        $this->itemDataMapper
            ->expects(self::once())
            ->method('find')
            ->willReturn($records);

        $response = $this->SUT->__invoke($request, []);

        self::assertSame(200, $response->getStatusCode());
        self::assertInstanceOf(JsonResponse::class, $response);
        $payload = $response->getPayload();
        self::assertArrayHasKey('items', $payload);
        self::assertEqualsCanonicalizing($records, $payload['items']);
    }

    public function testPagination(): void
    {
        $query = [
            'search' => 'foo',
            'page' => 2,
            'pageSize' => 10
        ];
        $request = (new ServerRequest())->withQueryParams($query);

        $this->itemDataMapper
            ->expects(self::once())
            ->method('find')
            ->with($query['search'], $query['page'], $query['pageSize']);

        $this->itemDataMapper
            ->expects(self::once())
            ->method('countPages')
            ->with($query['search'], $query['pageSize'])
            ->willReturn(3);

        $response = $this->SUT->__invoke($request, []);
        self::assertSame(200, $response->getStatusCode());
        self::assertInstanceOf(JsonResponse::class, $response);

        $payload = $response->getPayload();
        self::assertArrayHasKey('prev', $payload);
        self::assertArrayHasKey('next', $payload);
        self::assertArrayHasKey('totalPages', $payload);
        self::assertSame(1, $payload['prev']);
        self::assertSame(3, $payload['next']);
        self::assertSame(3, $payload['totalPages']);
    }

    public function testInvalidPageNumber(): void
    {
        $request = (new ServerRequest())->withQueryParams(['page' => 0]);
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Page must be greater than 0');

        $this->SUT->__invoke($request, []);
    }

    public function testInvalidPageSize(): void
    {
        $request = (new ServerRequest())->withQueryParams(['pageSize' => 0]);
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Page size must be between 1 and 100');

        $this->SUT->__invoke($request, []);
    }
}

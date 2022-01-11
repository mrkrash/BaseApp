<?php declare(strict_types=1);

namespace Mrkrash\Base\Http\RouteHandler;

use Doctrine\Instantiator\Exception\ExceptionInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;
use League\Route\Http\Exception\BadRequestException;
use League\Route\Http\Exception\NotFoundException;
use Mrkrash\Base\Model\Item;
use Mrkrash\Base\Model\ItemDataMapper;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Mrkrash\Base\Http\RouteHandler\ItemRead
 */
class ItemReadUnitTest extends TestCase
{
    private ItemRead $SUT;
    private ItemDataMapper $itemDataMapper;

    protected function setUp(): void
    {
        $this->itemDataMapper = $this->createMock(ItemDataMapper::class);
        $this->SUT = new ItemRead($this->itemDataMapper);
    }

    /**
     * @throws BadRequestException
     * @throws ExceptionInterface
     * @throws NotFoundException
     */
    public function testSuccess(): void
    {
        $item = new Item('foo');
        $request = new ServerRequest();
        $args = ['id' => 0];

        $this->itemDataMapper->method('findOne')->with($args['id'])->willReturn($item);
        $response = $this->SUT->__invoke($request, $args);

        self::assertSame(200, $response->getStatusCode());
        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertEquals($item, $response->getPayload());
    }

    /**
     * @throws NotFoundException
     * @throws ExceptionInterface
     */
    public function testInvalidUUID(): void
    {
        $request = new ServerRequest();
        $args = ['id' => 'foo'];

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Invalid ID');
        $this->SUT->__invoke($request, $args);
    }

    /**
     * @throws BadRequestException
     * @throws ExceptionInterface
     */
    public function testNotFound(): void
    {
        $request = new ServerRequest();
        $args = ['id' => 0];

        $this->itemDataMapper->method('findOne')->with($args['id'])->willReturn(null);
        $this->expectException(NotFoundException::class);
        $this->SUT->__invoke($request, $args);
    }
}

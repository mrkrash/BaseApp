<?php declare(strict_types=1);

namespace Mrkrash\Base\Http\RouteHandler;

use Doctrine\Instantiator\Exception\ExceptionInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;
use League\Route\Http\Exception\BadRequestException;
use League\Route\Http\Exception\NotFoundException;
use Mrkrash\Base\Model\InvalidDataException;
use Mrkrash\Base\Model\Item;
use Mrkrash\Base\Model\ItemDataMapper;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Mrkrash\Base\Http\RouteHandler\ItemUpdate
 */
class ItemUpdateUnitTest extends TestCase
{
    private ItemUpdate $SUT;
    private ItemDataMapper $itemDataMapper;

    protected function setUp(): void
    {
        $this->itemDataMapper = $this->createMock(ItemDataMapper::class);
        $this->SUT = new ItemUpdate($this->itemDataMapper);
    }

    public function testSuccess(): void
    {
        $item = new Item('foo');
        $data = [
            'name' => 'bar',
            'description' => 'A simple text'
        ];
        $request = (new ServerRequest())->withParsedBody($data);
        $args = ['id' => 0];

        $this->itemDataMapper->method('findOne')->with($args['id'])->willReturn($item);
        $this->itemDataMapper->expects(self::once())->method('update')->with($item);

        $response = $this->SUT->__invoke($request, $args);

        self::assertSame(200, $response->getStatusCode());
        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertEquals($item, $response->getPayload());
        self::assertSame($data['name'], $item->getName());
        self::assertSame($data['description'], $item->getDescription());
    }

    /**
     * @throws NotFoundException
     * @throws ExceptionInterface
     * @throws InvalidDataException
     */
    public function testInvalidID(): void
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
     * @throws InvalidDataException
     */
    public function testNotFound(): void
    {
        $request = new ServerRequest();
        $args = ['id' => 0];

        $this->itemDataMapper
            ->method('findOne')
            ->with($args['id'])
            ->willReturn(null)
        ;

        $this->expectException(NotFoundException::class);

        $this->SUT->__invoke($request, $args);
    }

    /**
     * @throws NotFoundException
     * @throws ExceptionInterface
     * @throws InvalidDataException
     */
    public function testInvalidRequestBody(): void
    {
        $item = new Item('foo');
        $request = new ServerRequest();
        $args = ['id' => 0];

        $this->itemDataMapper
            ->method('findOne')
            ->with($args['id'])
            ->willReturn($item)
        ;

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Invalid Request Body');

        $this->SUT->__invoke($request, $args);
    }
}

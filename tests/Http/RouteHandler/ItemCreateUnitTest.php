<?php declare(strict_types=1);

namespace Mrkrash\Base\Http\RouteHandler;

use Doctrine\Instantiator\Exception\ExceptionInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;
use League\Route\Http\Exception\BadRequestException;
use Mrkrash\Base\Model\InvalidDataException;
use Mrkrash\Base\Model\Item;
use Mrkrash\Base\Model\ItemDataMapper;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Mrkrash\Base\Http\RouteHandler\ItemCreate
 */
class ItemCreateUnitTest extends TestCase
{
    private ItemCreate $SUT;
    private ItemDataMapper $itemDataMapper;

    protected function setUp(): void
    {
        $this->itemDataMapper = $this->createMock(ItemDataMapper::class);
        $this->SUT = new ItemCreate($this->itemDataMapper);
    }

    /**
     * @throws BadRequestException
     * @throws ExceptionInterface
     * @throws InvalidDataException
     */
    public function testSuccess(): void
    {
        $data = [
            'name' => 'foo',
            'description' => 'A new sample',
        ];
        $request = (new ServerRequest())->withParsedBody($data);
        $this->itemDataMapper->expects(self::once())->method('insert');
        $response = $this->SUT->__invoke($request, []);

        self::assertSame(201, $response->getStatusCode());
        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertInstanceOf(Item::class, $response->getPayload());

        /** @var Item $item */
        $item = $response->getPayload();
        self::assertSame($data['name'], $item->getName());
        self::assertSame($data['description'], $item->getDescription());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidDataException
     */
    public function testInvalidRequestBody(): void
    {
        $request = new ServerRequest();

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Invalid request body');

        $this->SUT->__invoke($request, []);
    }
}

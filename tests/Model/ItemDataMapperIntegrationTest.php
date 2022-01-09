<?php declare(strict_types=1);

namespace Mrkrash\Base\Model;

use DI\DependencyException;
use DI\NotFoundException;
use Doctrine\Instantiator\Exception\ExceptionInterface;
use JsonException;
use Mrkrash\Base\App;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Mrkrash\Base\Model\ItemDataMapper
 */
class ItemDataMapperIntegrationTest extends TestCase
{
    private ItemDataMapper $SUT;

    /**
     * @throws DependencyException
     * @throws NotFoundException
     * @throws JsonException
     */
    protected function setUp(): void
    {
        $app = App::bootstrap();
        $this->SUT = $app->get(ItemDataMapper::class);
        $this->SUT->wipe();
    }

    /**
     * @throws ExceptionInterface
     */
    public function testAddAndFind(): void
    {
        $item = $this->SUT->insert(new Item("Foo"));
        self::assertEquals($item, $this->SUT->findOne($item->getId()));
    }

    public function testAddAndGetAll(): void
    {
        $item1 = $this->SUT->insert(new Item('foo'));
        $item2 = $this->SUT->insert(new Item('bar'));
        $items = $this->SUT->find();

        self::assertEqualsCanonicalizing([$item1, $item2], $items);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidDataException
     */
    public function testAddUpdateAndFind(): void
    {
        $item = $this->SUT->insert(new Item('foo'));
        $item->updateFromArray([
            'name' => 'bar',
            'description' => 'A simple description',
        ]);
        $this->SUT->update($item);

        self::assertEquals($item, $this->SUT->findOne($item->getId()));
    }

    public function testAddAndDelete(): void
    {
        $item = $this->SUT->insert(new Item('Foo'));
        self::assertEquals($item, $this->SUT->findOne($item->getId()));

        $this->SUT->delete($item);
        self::assertNull($this->SUT->findOne($item->getId()));
    }

    public function testFullTextSearch(): void
    {
        $item1 = $this->SUT->insert(new Item('foo'));
        $item2 = $this->SUT->insert(new Item('bar'));
        $item3 = $this->SUT->insert(new Item('bar baz'));

        self::assertEqualsCanonicalizing([$item1], $this->SUT->find("name LIKE '%foo%'"));
        self::assertEqualsCanonicalizing([$item2, $item3], $this->SUT->find("name LIKE '%bar%'"));
        self::assertEqualsCanonicalizing([$item3], $this->SUT->find("name LIKE '%baz%'"));
    }

    public function testPagination(): void
    {
        $item1 = $this->SUT->insert(new Item('foo'));
        $item2 = $this->SUT->insert(new Item('bar'));

        self::assertEqualsCanonicalizing([$item1, $item2], $this->SUT->find());
        self::assertEqualsCanonicalizing([$item2], $this->SUT->find(null, 2, 1));
        self::assertEqualsCanonicalizing([], $this->SUT->find(null, 2));
    }

    public function testCountPages(): void
    {
        $this->SUT->insert(new Item('foo'));
        $this->SUT->insert(new Item('bar'));
        $this->SUT->insert(new Item('bar baz'));
        $this->SUT->insert(new Item('bat'));

        self::assertSame(1, $this->SUT->countPages());
        self::assertSame(2, $this->SUT->countPages("name LIKE '%bar%'", 1));
        self::assertSame(2, $this->SUT->countPages(null, 2));
    }
}

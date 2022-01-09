<?php

namespace Mrkrash\Base\Model;

use DateTimeImmutable;
use Doctrine\Instantiator\Exception\ExceptionInterface;
use Lcobucci\Clock\FrozenClock;
use PHPUnit\Framework\TestCase;
use function Mrkrash\Base\now;
use const Mrkrash\Base\DATE_FORMAT;

/**
 * @covers \Mrkrash\Base\Model\Item
 */
class ItemUnitTest extends TestCase
{
    /**
     * @throws InvalidDataException
     */
    public function testCreatedAt(): void
    {
        $createdAt = new \DateTimeImmutable('@0');
        now(new FrozenClock($createdAt));
        $item = new Item("Foo");

        self::assertEquals($createdAt, $item->getCreatedAt());
    }

    /**
     * @dataProvider typeSafeInvalidDataProvider
     *
     * @param $name
     * @param $description
     * @param $deletedAt
     * @param array $invalidProperties
     * @return void
     */
    public function testValidation(
        $name,
        $description,
        $deletedAt,
        array $invalidProperties
    ): void {
        try {
            new Item($name, $description, $deletedAt);
        } catch (InvalidDataException $e) {
        }

        self::assertTrue(isset($e));
        self::assertEquals($invalidProperties, array_keys($e->getDetails()), 'Expected invalid properties don\'t match');
    }

    /**
     * @dataProvider invalidDataProvider
     *
     * @param $name
     * @param $description
     * @param $deletedAt
     * @param array $invalidProperties
     * @return void
     */
    public function disabledtestValidationFromArray(
        $name,
        $description,
        $deletedAt,
        array $invalidProperties
    ): void {
        try {
            Item::createFromArray(compact('name', 'description', 'deletedAt'));
        } catch (InvalidDataException|ExceptionInterface $e) {
        }

        self::assertTrue(isset($e));
        self::assertEquals($invalidProperties, array_keys($e->getDetails()), 'Expected invalid properties don\'t match');
    }

    public function invalidDataProvider(): array
    {
        return [
            ['', '', '', ['name', 'description', 'deletedAt']],
            ['', 'Foo', $this->createDateTimeString(), ['name']],
            ['Foo', '', '', ['description', 'deletedAt']],
            ['Foo', 'Bar', '', ['deletedAt']],
            [' ', 'Foo', '', ['name', 'deletedAt']],
        ];
    }

    public function typeSafeInvalidDataProvider(): array
    {
        return [
            ['', 'Foo', $this->createDateTimeString(), ['name']],
            ['', 'Foo', $this->createDateTimeString(), ['name']],
        ];
    }

    private function createDateTimeString(int $timestamp = 0): string
    {
        return (new DateTimeImmutable("@$timestamp"))->format(DATE_FORMAT);
    }
}

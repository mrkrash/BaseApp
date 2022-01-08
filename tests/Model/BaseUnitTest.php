<?php

namespace Mrkrash\Base\Model;

use DateTimeImmutable;
use Lcobucci\Clock\FrozenClock;
use PHPUnit\Framework\TestCase;
use function Mrkrash\Base\now;
use const Mrkrash\Base\DATE_FORMAT;

/**
 * @covers \Mrkrash\Base\Model\Base
 */
class BaseUnitTest extends TestCase
{
    /**
     * @throws InvalidDataException
     */
    public function testCreatedAt(): void
    {
        $createdAt = new \DateTimeImmutable('@0');
        now(new FrozenClock($createdAt));
        $item = new Base(1, $createdAt->format(DATE_FORMAT));

        self::assertEquals($createdAt, $item->getCreatedAt());
    }

    /**
     * @dataProvider typeSafeInvalidDataProvider
     *
     * @param $number
     * @param $date
     * @param $validity
     * @param $discount
     * @param $accepted
     * @param array $invalidProperties
     * @return void
     */
    public function testValidation(
        $number,
        $date,
        $validity,
        $discount,
        $accepted,
        array $invalidProperties
    ): void {
        try {
            new Base($number, $date, $validity, $discount, $accepted);
        } catch (InvalidDataException $e) {
        }

        self::assertTrue(isset($e));
        self::assertEquals($invalidProperties, array_keys($e->getDetails()), 'Expected invalid properties don\'t match');
    }

    /**
     * @dataProvider invalidDataProvider
     *
     * @param $number
     * @param $date
     * @param $validity
     * @param $discount
     * @param $accepted
     * @param array $invalidProperties
     * @return void
     */
    public function disabledtestValidationFromArray(
        $number,
        $date,
        $validity,
        $discount,
        $accepted,
        array $invalidProperties
    ): void {
        try {
            Base::createFromArray(compact('number', 'date', 'validity', 'discount', 'accepted'));
        } catch (InvalidDataException $e) {
        }

        self::assertTrue(isset($e));
        self::assertEquals($invalidProperties, array_keys($e->getDetails()), 'Expected invalid properties don\'t match');
    }

    public function invalidDataProvider(): array
    {
        return [
            //[0, '', 0, '', '', ['number', 'date', 'accepted']],
            [-1, $this->createDateTimeString(), 10, '10', $this->createDateTimeString(), ['number']],
            //[1, '', 10, '10', '', ['date', 'accepted']],
            //[1, '1970-01-01', 10, '', '', ['accepted']],
            [0, $this->createDateTimeString(), 1, '', '', ['number', 'accepted']],
        ];
    }

    public function typeSafeInvalidDataProvider(): array
    {
        return [
            [0, $this->createDateTimeString(), 10, '10', $this->createDateTimeString(), ['number']],
            [-1, $this->createDateTimeString(), 10, null, $this->createDateTimeString(), ['number']],
        ];
    }

    private function createDateTimeString(int $timestamp = 0): string
    {
        return (new DateTimeImmutable("@$timestamp"))->format(DATE_FORMAT);
    }
}

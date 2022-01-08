<?php

namespace Mrkrash\Estimate\Model;

use Assert\Assert;
use Assert\LazyAssertionException;
use DateTimeImmutable;
use Doctrine\Instantiator\Exception\ExceptionInterface;
use Doctrine\Instantiator\Instantiator;
use function Mrkrash\Estimate\datetime_from_string;
use function Mrkrash\Estimate\now;
use const Mrkrash\Estimate\DATE_FORMAT;

class Estimate extends Model
{
    protected int $id;
    protected DateTimeImmutable $createdAt;
    private int $number;
    private ?int $validity;
    private ?string $discount;
    private DateTimeImmutable $date;
    private ?DateTimeImmutable $accepted;

    /**
     * @throws InvalidDataException
     */
    public function __construct(
        int $number,
        string $date,
        int $validity = null,
        string $discount = null,
        string $accepted = null
    ) {
        parent::__construct();
        $this->createdAt = now();

        $this->validate(compact('number', 'date'));

        $this->number = $number;
        $this->validity = $validity;
        $this->discount = $discount;
        $this->date = datetime_from_string($date);
        $this->accepted = $accepted ? datetime_from_string($accepted) : null;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function withId(int $id): self
    {
        $new = clone $this;
        $new->id = $id;

        return $new;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getCreatedAtAsString(): string
    {
        return $this->createdAt->format(DATE_FORMAT);
    }

    public function withCreatedAt(string $createdAt): self
    {
        $new = clone $this;
        $new->createdAt = datetime_from_string($createdAt);

        return $new;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function getValidity(): int
    {
        return $this->validity;
    }

    /**
     * @return string|null
     */
    public function getDiscount(): ?string
    {
        return $this->discount;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function getDateAsString(): string
    {
        return $this->date->format(DATE_FORMAT);
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getAccepted(): ?DateTimeImmutable
    {
        return $this->accepted;
    }

    public function getAcceptedAsString(): string
    {
        return $this->accepted ? $this->accepted->format(DATE_FORMAT) : '';
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'number' => $this->number,
            'date' => $this->getDateAsString(),
            'validity' => $this->validity,
            'discount' => $this->discount,
            'accepted' => $this->getAcceptedAsString() ?: null,
            'createdAt' => $this->getCreatedAtAsString(),
        ];
    }

    /**
     * This method is intended as a type-unsafe alternative to the constructor
     * @throws ExceptionInterface
     * @throws InvalidDataException
     */
    public static function createFromArray(array $data): self
    {
        /**
         * we use this to avoid double validation.
         * @var self $new
         */
        $new = (new Instantiator)->instantiate(__CLASS__);
        $new->number = $data['number'];
        $new->date = datetime_from_string($data['date']);
        $new->validity = $data['validity'];
        $new->discount = $data['discount'];
        $new->accepted = datetime_from_string($data['accepted']);

        $new->setAssert(Assert::lazy()->tryAll());
        $new->validate($data);

        return $new;
    }

    /**
     * @param array $data
     * @throws InvalidDataException
     */
    private function validate(array $data): void
    {
        if (isset($data['number'])) {
            $this->assert->that($data['number'], 'number')->integer()->min(1);
        }
        if (isset($data['date'])) {
            $this->assert->that($data['date'], 'date')->date(DATE_FORMAT);
        }
        if (isset($data['validity'])) {
            $this->assert->that($data['validity'], 'validity')->nullOr()->integer();
        }
        if (isset($data['discount'])) {
            $this->assert->that($data['discount'], 'discount')->nullOr()->string()->notBlank();
        }
        if (isset($data['accepted'])) {
            $this->assert->that($data['accepted'], 'accepted')->date(DATE_FORMAT);
        }

        try {
            $this->assert->verifyNow();
        } catch (LazyAssertionException $e) {
            throw InvalidDataException::fromLazyAssertionException($e);
        }
    }
}
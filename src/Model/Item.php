<?php

namespace Mrkrash\Base\Model;

use Assert\Assert;
use Assert\LazyAssertionException;
use DateTimeImmutable;
use Doctrine\Instantiator\Exception\ExceptionInterface;
use Doctrine\Instantiator\Instantiator;
use function Mrkrash\Base\datetime_from_string;
use function Mrkrash\Base\now;
use const Mrkrash\Base\DATE_FORMAT;

class Item extends Model
{
    private int $id;
    private string $name;
    private ?string $description;
    private DateTimeImmutable $createdAt;
    private ?DateTimeImmutable $deletedAt;

    /**
     * @throws InvalidDataException
     */
    public function __construct(
        string $name,
        string $description = null,
        string $deletedAt = null
    ) {
        parent::__construct();
        $this->createdAt = now();

        $this->validate(compact('name'));

        $this->name = $name;
        $this->description = $description;
        $this->deletedAt = $deletedAt ? datetime_from_string($deletedAt) : null;
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

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function getDeletedAtAsString(): string
    {
        return $this->deletedAt ? $this->deletedAt->format(DATE_FORMAT) : '';
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->name,
            'description' => $this->description,
            'deletedAt' => $this->getDeletedAtAsString() ?: null,
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
        $new->name = $data['number'];
        $new->description = $data['description'];
        $new->deletedAt = datetime_from_string($data['deletedAt']);

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
        if (isset($data['name'])) {
            $this->assert->that($data['name'], 'name')->string()->notBlank();
        }
        if (isset($data['description'])) {
            $this->assert->that($data['description'], 'description')->nullOr()->string()->notBlank();
        }
        if (isset($data['deletedAt'])) {
            $this->assert->that($data['deletedAt'], 'deletedAt')->date(DATE_FORMAT);
        }

        try {
            $this->assert->verifyNow();
        } catch (LazyAssertionException $e) {
            throw InvalidDataException::fromLazyAssertionException($e);
        }
    }
}
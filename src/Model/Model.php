<?php

namespace Mrkrash\Estimate\Model;

use Assert\Assert;
use Assert\LazyAssertion;
use DateTimeImmutable;
use JsonSerializable;

abstract class Model implements JsonSerializable
{
    protected LazyAssertion $assert;

    public function __construct()
    {
        $this->assert = Assert::lazy()->tryAll();
    }

    public function setAssert(LazyAssertion $assert)
    {
        $this->assert = $assert;
    }

    abstract public function getId(): int;
    abstract public function withId(int $id): self;
    abstract public function getCreatedAt(): DateTimeImmutable;
    abstract public function getCreatedAtAsString(): string;
    abstract public function withCreatedAt(string $createdAt): self;

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
    }
}
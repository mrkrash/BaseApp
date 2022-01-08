<?php

namespace Mrkrash\Base\Model;

use Assert\Assert;
use Closure;
use Doctrine\Instantiator\Exception\ExceptionInterface;
use Exception;
use RedBeanPHP\OODB;
use RedBeanPHP\OODBBean;
use RedBeanPHP\ToolBox;

class BaseMapper
{
    public const DEFAULT_PAGE_SIZE = 20;

    private OODB $odb;

    public function __construct(ToolBox $toolBox)
    {
        $this->odb = $toolBox->getRedBean();
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function findOne(int $id): Base
    {
        $bean = $this->odb->load('Base', $id);
        if ($bean === null) {
            throw new Exception("Data Not Found");
        }

        return $this->createBaseFromBeam($bean);
    }

    public function find(string $search = null, int $page = 1, int $pageSize = self::DEFAULT_PAGE_SIZE): array
    {
        Assert::that($page)->greaterThan(0);
        Assert::that($pageSize)->greaterThan(0);
        $where = ($search) ? " WHERE {$search} " : null;
        $offset = ($page - 1) * $pageSize;
        $limit = $pageSize;
        $rows = $this->odb->find('Base', "{$where} OFFSET {$offset} LIMIT {$limit}");

        $baseFactory = Closure::fromCallable([$this, 'createBaseFromBeam']);

        return array_map($baseFactory, $rows);
    }

    public function countPages(string $search = null, int $pageSize = self::DEFAULT_PAGE_SIZE): int
    {
        Assert::that($pageSize)->greaterThan(0);
        $where = ($search) ? " WHERE {$search} " : null;
        $count = $this->odb->count('Base', $where);
        if ($count <= $pageSize) {
            return 1;
        }
        return (int) ceil($count / $pageSize);
    }

    public function insert(Base $base): Base
    {
        $bean = $this->populateData($this->odb->dispense('Base'), $base);
        $id = $this->odb->store($bean);

        return $base->withId($id);
    }

    public function update(Base $base): void
    {
        $bean = $this->populateData($this->odb->load('Base', $base->getId()), $base);
        $this->odb->store($bean);
    }

    public function delete(Base $base): void
    {
        $this->odb->trash($this->odb->load('Base', $base->getId()));
    }

    /**
     * @throws ExceptionInterface
     */
    private function createBaseFromBeam(OODBBean $bean): Base
    {
        return Base::createFromArray([
            'number' => $bean->number,
            'date' => $bean->date,
            'validity' => $bean->validity,
            'discount' => $bean->discount,
            'accepted' => $bean->accepted,
        ])->withId($bean->id)->withCreatedAt($bean->created_at);
    }

    private function populateData(OODBBean $bean, Base $base): OODBBean
    {
        $bean->number = $base->getNumber();
        $bean->date = $base->getDateAsString();
        $bean->validity = $base->getValidity();
        $bean->discount = $base->getDiscount();
        $bean->accepted = $base->getAcceptedAsString();
        $bean->created_at = $base->getCreatedAtAsString();

        return $bean;
    }
}
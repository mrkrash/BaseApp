<?php

namespace Mrkrash\Estimate\Model;

use Assert\Assert;
use Closure;
use Doctrine\Instantiator\Exception\ExceptionInterface;
use Exception;
use RedBeanPHP\OODB;
use RedBeanPHP\OODBBean;
use RedBeanPHP\ToolBox;

class EstimateMapper
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
    public function findOne(int $id): Estimate
    {
        $bean = $this->odb->load('estimate', $id);
        if ($bean === null) {
            throw new Exception("Data Not Found");
        }

        return $this->createEstimateFromBeam($bean);
    }

    public function find(string $search = null, int $page = 1, int $pageSize = self::DEFAULT_PAGE_SIZE): array
    {
        Assert::that($page)->greaterThan(0);
        Assert::that($pageSize)->greaterThan(0);
        $where = ($search) ? " WHERE {$search} " : null;
        $offset = ($page - 1) * $pageSize;
        $limit = $pageSize;
        $rows = $this->odb->find('estimate', "{$where} OFFSET {$offset} LIMIT {$limit}");

        $estimateFactory = Closure::fromCallable([$this, 'createEstimateFromBeam']);

        return array_map($estimateFactory, $rows);
    }

    public function countPages(string $search = null, int $pageSize = self::DEFAULT_PAGE_SIZE): int
    {
        Assert::that($pageSize)->greaterThan(0);
        $where = ($search) ? " WHERE {$search} " : null;
        $count = $this->odb->count('estimate', $where);
        if ($count <= $pageSize) {
            return 1;
        }
        return (int) ceil($count / $pageSize);
    }

    public function insert(Estimate $estimate): Estimate
    {
        $bean = $this->populateData($this->odb->dispense('estimate'), $estimate);
        $id = $this->odb->store($bean);

        return $estimate->withId($id);
    }

    public function update(Estimate $estimate): void
    {
        $bean = $this->populateData($this->odb->load('estimate', $estimate->getId()), $estimate);
        $this->odb->store($bean);
    }

    public function delete(Estimate $estimate): void
    {
        $this->odb->trash($this->odb->load('estimate', $estimate->getId()));
    }

    /**
     * @throws ExceptionInterface
     */
    private function createEstimateFromBeam(OODBBean $bean): Estimate
    {
        return Estimate::createFromArray([
            'number' => $bean->number,
            'date' => $bean->date,
            'validity' => $bean->validity,
            'discount' => $bean->discount,
            'accepted' => $bean->accepted,
        ])->withId($bean->id)->withCreatedAt($bean->created_at);
    }

    private function populateData(OODBBean $bean, Estimate $estimate): OODBBean
    {
        $bean->number = $estimate->getNumber();
        $bean->date = $estimate->getDateAsString();
        $bean->validity = $estimate->getValidity();
        $bean->discount = $estimate->getDiscount();
        $bean->accepted = $estimate->getAcceptedAsString();
        $bean->created_at = $estimate->getCreatedAtAsString();

        return $bean;
    }
}
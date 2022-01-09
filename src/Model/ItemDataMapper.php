<?php declare(strict_types=1);

namespace Mrkrash\Base\Model;

use Assert\Assert;
use Closure;
use Doctrine\Instantiator\Exception\ExceptionInterface;
use Exception;
use RedBeanPHP\OODB;
use RedBeanPHP\OODBBean;
use RedBeanPHP\R;
use RedBeanPHP\ToolBox;

class ItemDataMapper
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
    public function findOne(int $id): ?Item
    {
        $bean = $this->odb->load('Item', $id);
        if ($bean->getID() == 0) {
            return null;
        }

        return $this->createItemFromBeam($bean);
    }

    public function find(string $search = null, int $page = 1, int $pageSize = self::DEFAULT_PAGE_SIZE): array
    {
        Assert::that($page)->greaterThan(0);
        Assert::that($pageSize)->greaterThan(0);
        $where = ($search) ? " WHERE {$search} " : null;
        $offset = ($page - 1) * $pageSize;
        $limit = $pageSize;
        $rows = $this->odb->find('Item', null," {$where} LIMIT {$limit} OFFSET {$offset}");

        $itemFactory = Closure::fromCallable([$this, 'createItemFromBeam']);

        return array_map($itemFactory, $rows);
    }

    public function countPages(string $search = null, int $pageSize = self::DEFAULT_PAGE_SIZE): int
    {
        Assert::that($pageSize)->greaterThan(0);
        $where = ($search) ? " WHERE {$search} " : null;
        $count = count($this->odb->find('Item', null,$where));
        if ($count <= $pageSize) {
            return 1;
        }
        return (int) ceil($count / $pageSize);
    }

    public function insert(Item $item): Item
    {
        $bean = $this->populateData($this->odb->dispense('Item'), $item);
        $id = $this->odb->store($bean);

        return $item->withId($id);
    }

    public function update(Item $item): void
    {
        $bean = $this->populateData($this->odb->load('Item', $item->getId()), $item);
        $this->odb->store($bean);
    }

    public function delete(Item $item): void
    {
        $this->odb->trash($this->odb->load('Item', $item->getId()));
    }

    public function wipe(): void
    {
        $this->odb->wipe('item');
    }

    /**
     * @throws ExceptionInterface|InvalidDataException
     */
    private function createItemFromBeam(OODBBean $bean): Item
    {
        return Item::createFromArray([
            'name' => $bean->name,
            'description' => $bean->description ?: null,
            'deletedAt' => $bean->deleted_at ?: null,
        ])->withId((int) $bean->id)->withCreatedAt($bean->created_at);
    }

    private function populateData(OODBBean $bean, Item $item): OODBBean
    {
        $bean->name = $item->getName();
        $bean->description = $item->getDescription();
        $bean->deleted_at = $item->getdeletedAtAsString();
        $bean->created_at = $item->getCreatedAtAsString();

        return $bean;
    }
}
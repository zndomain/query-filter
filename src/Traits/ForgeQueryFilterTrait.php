<?php

namespace ZnDomain\QueryFilter\Traits;

use Psr\EventDispatcher\EventDispatcherInterface;
use ZnDomain\Domain\Enums\EventEnum;
use ZnDomain\Domain\Events\QueryEvent;
use ZnDomain\Query\Entities\Query;
use ZnDomain\QueryFilter\Helpers\FilterModelHelper;

trait ForgeQueryFilterTrait
{

    abstract protected function getEventDispatcher(): EventDispatcherInterface;

    public function forgeQueryByFilter(object $filterModel, Query $query)
    {
        FilterModelHelper::validate($filterModel);
        FilterModelHelper::forgeOrder($query, $filterModel);
        $query = $this->forgeQuery($query);
        $event = new QueryEvent($query);
        $event->setFilterModel($filterModel);
        $this
            ->getEventDispatcher()
            ->dispatch($event, EventEnum::BEFORE_FORGE_QUERY_BY_FILTER);
        $schema = $this->getSchema();
        $columnList = $schema->getColumnListing($this->tableNameAlias());
        FilterModelHelper::forgeCondition($query, $filterModel, $columnList);
    }
}

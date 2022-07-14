<?php

namespace ZnDomain\QueryFilter\Helpers;

use ZnCore\Arr\Helpers\ArrayHelper;
use ZnCore\Entity\Helpers\EntityHelper;
use ZnCore\Query\Entities\Query;
use ZnCore\Query\Entities\Where;
use ZnDomain\QueryFilter\Exceptions\BadFilterValidateException;
use ZnDomain\QueryFilter\Interfaces\DefaultSortInterface;
use ZnDomain\QueryFilter\Interfaces\IgnoreAttributesInterface;
use ZnCore\Validation\Exceptions\UnprocessibleEntityException;
use ZnCore\Validation\Helpers\ValidationHelper;

class FilterModelHelper
{

    public static function validate(object $filterModel)
    {
        try {
            ValidationHelper::validateEntity($filterModel);
        } catch (UnprocessibleEntityException $e) {
            $exception = new BadFilterValidateException();
            $exception->setErrorCollection($e->getErrorCollection());
            throw new $exception;
        }
    }

    public static function forgeCondition(Query $query, object $filterModel, array $attributesOnly)
    {
        $params = EntityHelper::toArrayForTablize($filterModel);
        if ($filterModel instanceof IgnoreAttributesInterface) {
            $filterParams = $filterModel->ignoreAttributesFromCondition();
            foreach ($params as $key => $value) {
                if (in_array($key, $filterParams)) {
                    unset($params[$key]);
                }
            }
        } else {
            $params = ArrayHelper::extractByKeys($params, $attributesOnly);
        }
        foreach ($params as $paramsName => $paramValue) {
            if ($paramValue !== null) {
                $query->whereNew(new Where($paramsName, $paramValue));
            }
        }
    }

    public static function forgeOrder(Query $query, object $filterModel)
    {
        $sort = $query->getParam(Query::ORDER);
        if (empty($sort) && $filterModel instanceof DefaultSortInterface) {
            $sort = $filterModel->defaultSort();
            $query->orderBy($sort);
        }
    }
}

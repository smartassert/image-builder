<?php

namespace App\Services;

use App\Model\Filter;

class FilterStringParser
{
    /**
     * @return Filter[]
     */
    public function parse(string $filter): array
    {
        $filterCollectionData = json_decode($filter, true);
        if (!is_array($filterCollectionData)) {
            return [];
        }

        $filters = [];
        foreach ($filterCollectionData as $filterData) {
            $filter = Filter::fromArray($filterData);
            if ($filter instanceof Filter) {
                $filters[] = $filter;
            }
        }

        return $filters;
    }
}

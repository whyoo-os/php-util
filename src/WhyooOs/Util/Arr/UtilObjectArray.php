<?php

namespace WhyooOs\Util\Arr;

use WhyooOs\Util\UtilDebug;
use WhyooOs\Util\UtilDict;
use WhyooOs\Util\UtilDocument;

/**
 * utility functions for handling lists (aka arrays) of objects (with no getters/setters)
 *
 * 10/2021 created
 */
class UtilObjectArray
{

    /**
     * 10/2021 created
     *
     * @param array $items
     * @param string $columnName eg "id"
     * @return array
     */
    public static function arrayColumn(array $items, string $columnName): array
    {
        return array_map(function ($f) use ($columnName) {
            return $f->$columnName;
        }, $items);
    }


}

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



    /**
     * 07/2018
     * 08/2022 moved from UtilArray::_matchCriteria() --> UtilObjectArray::_matchCriteria()
     *
     * @param $item
     * @param $criteria
     * @return bool
     */
    private static function _matchCriteria($item, array $criteria): bool
    {
        foreach ($criteria as $key => $val) {
            if ($item->$key != $val) {
                return false;
            }
        }

        return true;
    }




    /**
     * 07/2018 signature changed: replaced $key, $value with $criteria
     * 08/2022 moved to from UtilArray::findOne() --> UtilObjectArray::findOne()
     *
     * @param array|\Iterator $arr
     * @param array $criteria
     * @return mixed|null
     */
    public static function findOne($arr, array $criteria)
    {
        foreach ($arr as $item) {
            if (self::_matchCriteria($item, $criteria)) {
                return $item;
            }
        }

        return null;
    }


    /**
     * 07/2018
     * 08/2022 moved to from UtilArray::findMany() --> UtilObjectArray::findMany()
     * used by Schlegel, untested
     *
     * @param array $arr
     * @param array $criteria
     * @return array
     */
    public static function findMany($arr, array $criteria): array
    {
        $ret = [];
        foreach ($arr as &$item) {
            if (self::_matchCriteria($item, $criteria)) {
                $ret[] = $item;
            }
        }

        return $ret;
    }
    
    
}

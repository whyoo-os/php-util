<?php

namespace WhyooOs\Util\Arr;

/**
 * utility functions for handling lists (aka arrays) of associative arrays (aka dicts)
 *
 * 05/2021 created
 */
class UtilDictArray
{
    /**
     * aka numeric2assoc
     *
     * 05/2021 moved from UtilArray::arrayOfArraysToAssoc() to UtilDictArray::dictArrayToDict()
     *
     * @param array $array
     * @param string $keyName
     * @return array assoc array
     */
    public static function dictArrayToDict(array $array, string $keyName): array
    {
        $values = array_values($array);
        $keys = array_column($values, $keyName);

        return array_combine($keys, $values);
    }

    /**
     * 05/2021 created, used by push4 graph building
     *
     * @param array $allowedFunctionNames
     * @param array $criteria eg ['function' => $allowedFunctionNames]
     */
    public static function filter(array $allowedFunctionNames, array $criteria)
    {
    }


}

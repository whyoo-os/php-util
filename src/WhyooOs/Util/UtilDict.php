<?php

namespace WhyooOs\Util;

/**
 * 12/2019
 */
class UtilDict
{



    /**
     * 12/2019
     */
    public static function getOneFilteredByWhitelist(array $one, $whitelistedKeys)
    {
        $newDict = [];
        foreach($whitelistedKeys as $key) {
            $newDict[$key] = $one[$key];
        }

        return $newDict;
    }


    /**
     * 12/2019 unimplemented
     *
     * @param array $arr
     * @param array $blacklistedKeys
     * @throws \Exception
     */
    public static function getManyFilteredByBlacklist($arr, array $blacklistedKeys)
    {
        throw new \Exception("TODO...");
    }


    /**
     * 12/2019
     *
     * @param array[] $many
     * @param array $whitelistedKeys
     * @return array[]
     */
    public static function getManyFilteredByWhitelist(iterable $many, array $whitelistedKeys)
    {
        $newList = [];
        foreach($many as &$item) {
            $newList[] = self::getOneFilteredByWhitelist($item, $whitelistedKeys);
        }

        return $newList;
    }


    /**
     * 12/2019 moved from UtilArray to here .. because list of dicts is sorted
     *
     * @param array $array
     * @param string $key
     * @param array $order
     * @return array
     */
    public static function sortManyByCustomOrder(array $array, string $key, array $order)
    {
        usort($array, function ($a, $b) use ($order, $key) {
            $pos_a = array_search($a[$key], $order);
            $pos_b = array_search($b[$key], $order);
            return $pos_a - $pos_b;
        });

        return $array;
    }


    /**
     * 08/2020
     *
     * @param array $arr
     * @param string $prefix
     * @return array|false
     */
    public static function prependToKeys(array $arr, string $prefix)
    {
        $keys = array_map(function($key) use ($prefix){
            return $prefix . $key;
        }, array_keys($arr));

        return array_combine($keys, array_values($arr));
    }


    /**
     * 08/2020
     *
     * @param array $arr
     * @param string $prefix
     * @return array|false
     */
    public static function appendToKeys(array $arr, string $suffix)
    {
        $keys = array_map(function($key) use ($suffix){
            return $key . $suffix;
        }, array_keys($arr));

        return array_combine($keys, array_values($arr));
    }


}

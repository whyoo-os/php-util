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




}

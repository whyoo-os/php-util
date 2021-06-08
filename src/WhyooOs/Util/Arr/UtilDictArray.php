<?php

namespace WhyooOs\Util\Arr;

use WhyooOs\Util\UtilDebug;
use WhyooOs\Util\UtilDict;

/**
 * utility functions for handling lists (aka arrays) of associative arrays (aka dicts)
 *
 * 05/2021 created
 */
class UtilDictArray
{
    /**
     * aka numeric2assoc, aka _.keyBy (or _.indexBy in older versions)
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
     * get single property of dicts in an array,
     * also by sub-dicts like 'userProfile.birthday' are possible
     *
     * example:
     *
     * $posts = {'a' => post1, 'b' => post2, 'c' => post3]
     * arrayColumnDeep($posts, 'id', false) returns [1,2,3]
     * arrayColumnDeep($posts, 'id', true) returns { a:1, b:2, c:3 }
     *
     * 05/2021 created (a modified copy of UtilDocumentArray::arrayColumnDeep())
     *
     * @param array $arr
     * @param string $path also sub-documents like 'userProfile.birthday' are possible
     * @param bool $bKeepOriginalKeys the thing with $bKeepOriginalKeys` is if array is associative to keep the old keys not to cretae new numeric array
     * @return array
     */
    public static function arrayColumnDeep(array $arr, string $path, bool $bKeepOriginalKeys = false): array
    {
        $subfields = explode('.', $path);

        if ($bKeepOriginalKeys) {
            // keep original keys
            array_walk($arr, function (&$item, $key) use ($subfields) {
                foreach ($subfields as $fieldName) {
                    $item = $item[$fieldName] ?? null;
                }
            });
            $newArray = $arr;
        } else {
            // new keys (create ordinary numeric array)
            $newArray = array_map(function ($item) use ($subfields) {
                foreach ($subfields as $fieldName) {
                    $item = $item[$fieldName] ?? null;
                }
                return $item;
            }, $arr);
        }

        return $newArray;
    }

    /**
     * 05/2021 created, used by push4 graph building
     *
     * @param array $arr
     * @param string $path eg 'function', also deepPaths are possible 'xx.yy.zz'
     * @param array $whitelist eg ['LicMapScalar', 'RotateWarpMapScalar']
     * @return array
     */
    public static function whitelist(array $arr, string $path, array $whitelist): array
    {
        return array_filter($arr, function ($item) use ($path, $whitelist) {
            try {
                return in_array(UtilDict::deepGet($item, $path, True), $whitelist);
            } catch (\Exception $e) {
                return false;
            }
        });

    }


    /**
     * 05/2021 created, used by push4 graph building
     *
     * @param array $arr
     * @param string $path eg 'function', also deepPaths are possible 'xx.yy.zz'
     * @param array $blacklist eg ['DivisionRaster', 'DrawRectangles']
     * @return array
     */
    public static function blacklist(array $arr, string $path, array $blacklist): array
    {
        return array_filter($arr, function ($item) use ($path, $blacklist) {
            try {
                return !in_array(UtilDict::deepGet($item, $path, True), $blacklist);
            } catch (\Exception $e) {
                return True;
            }
        });
    }


}

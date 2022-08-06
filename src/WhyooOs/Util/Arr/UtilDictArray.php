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


    /**
     * 06/2021 used for import coaches mb (requirement: do not import duplicate coaches with same email)
     *
     * @param array $arr the dictArray
     * @param string $fieldName eg 'email'
     * @param bool $bTrim trim the field before comparison
     * @param bool $bCaseInsensitive strlower the field before comparison
     * @return array dict with the fieldValue as $key and array with the found entry as values
     *
     * example for grouping an dict array by field 'email'
     *
     * in:
     *
     *       [
     *           {
     *               email: 'aaa',
     *               id:    1
     *           },
     *           {
     *               email: 'aaa',
     *               id:    2
     *           },
     *           {
     *               email: 'bbb',
     *               id:    3
     *           }
     *       ]
     *
     * out:
     *
     *       {
     *           aaa: [
     *               {
     *                   email: 'aaa',
     *                   id:    1
     *               },
     *               {
     *                   email: 'aaa',
     *                   id:    2
     *               }
     *           ],
     *           bbb: [
     *               {
     *                   email: 'bbb',
     *                   id:    3
     *               }
     *           ]
     *       }
     *
     */
    public static function grouped(array $arr, string $fieldName, bool $bTrim=true, bool $bCaseInsensitive=true): array
    {
        $groupedByFieldName = [];
        foreach ($arr as &$v) {
            $key = $v[$fieldName];
            if($bTrim) {
                $key = trim($key);
            }
            if($bCaseInsensitive) {
                $key = mb_strtolower($key);
            }
            if (!isset($groupedByFieldName[$key])) {
                $groupedByFieldName[$key] = [&$v];
            } else {
                $groupedByFieldName[$key][] = &$v;
            }
        }

        return $groupedByFieldName;
    }




    /**
     * 07/2018
     * 08/2022 moved to from UtilArray::_matchCriteria() --> UtilDictArray::_matchCriteria()
     *
     * @param $item
     * @param $criteria
     * @return bool
     */
    private static function _matchCriteria($item, array $criteria): bool
    {
        foreach ($criteria as $key => $val) {
            if ($item[$key] != $val) {
                return false;
            }
        }

        return true;
    }




    /**
     * 07/2018 signature changed: replaced $key, $value with $criteria
     * 08/2022 moved to from UtilArray::findOne() --> UtilDictArray::findOne()
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
     * 08/2022 moved to from UtilArray::findMany() --> UtilDictArray::findMany()
     *
     * @param array|\Iterator $arr
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

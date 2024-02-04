<?php

namespace WhyooOs\Util;

use WhyooOs\Util\Arr\UtilArray;

/**
 * utility functions for handling associative arrays (aka dicts)
 *
 * 12/2019
 */
class UtilDict
{


    /**
     *  Block list
     *    Remove the values you don't want
     *    var result = _.omit(credentials, ['age']);
     *
     * 02/2023 created (TopData)
     *
     * @param $dict
     * @param string[] $blockList list of "blocked" dict keys (blacklist)
     * @return array
     */
    public static function omit($dict, array $blockList): array
    {
        $ret = [];
        foreach($dict as $key => $value) {
            if(!in_array($key, $blockList))
            $ret[$key] = $value;
        }

        return $ret;
    }

    /**
     * recursive helper
     */
    private static function _pick(array $src, array &$dest, array $allowList)
    {
        foreach ($allowList as $path) {

            if (str_contains($path, '.')) {
                // ---- with sub keys
                [$mainKey, $remainingKey] = explode('.', $path, 2);
                if (!array_key_exists($mainKey, $src)) {
                    continue;
                }
                // ---- pick recursively
                $picked = [];
                self::_pick($src[$mainKey], $picked, [$remainingKey]);
                if (!empty($picked)) {
                    if (array_key_exists($mainKey, $dest) && is_array($dest[$mainKey])) {
                        $dest[$mainKey] = array_merge_recursive($dest[$mainKey], $picked);
                        // UtilDebug::d("------------------------------------------------", $picked, $dest);
                    } else {
                        $dest[$mainKey] = $picked;
                    }
                }
            } else {
                // ---- without sub keys
                if (!array_key_exists($path, $src)) {
                    continue;
                }
                if (array_key_exists($path, $dest) && is_array($dest[$path]) && is_array($src[$path])) {
                    // merge 2 arrays
                    $dest[$path] = array_merge_recursive($dest[$path], $src[$path]);
                } else {
                    $dest[$path] = $src[$path];
                }
            }

        }
    }

    /**
     * Allow list
     *   Only allow certain values
     *   var result = _.pick(credentials, ['fname', 'lname']);
     *
     * 02/2023 created (TopData)
     * 09/2023 now supports sub-keys (separated by dot), eg 'customFields.topdata_order_approval_phone_number'
     *
     * @param array $src
     * @param string[] $allowList list of allowed dict keys (whitelist)
     * @return void
     */
    public static function pick(array $src, array $allowList): array
    {
        $dest = [];
        self::_pick($src, $dest, $allowList);

        return $dest;
    }



    /**
     * used by mb
     * TODO: add support for dots in paths
     *
     * 12/2019 created
     * 11/2021 parameter $bSkipNonExisting added (true for PATCH, false for PUT/POST)
     *
     * @param array $src
     * @param string[] $whitelistedKeys list of paths, dots are supported
     * @param bool $bSkipNonExisting skip if a field from whitelist does not exist in srcArray if true, set it to NULL if false
     * @return array the newly created assoc array (aka dict)
     */
    public static function getOneFilteredByWhitelist(array $src, array $whitelistedKeys, bool $bSkipNonExisting = false)
    {
        $newDict = [];
        foreach ($whitelistedKeys as $key) {
            if (array_key_exists($key, $src)) {
                $newDict[$key] = $src[$key];
            } else {
                if (!$bSkipNonExisting) {
                    // set explicitly to null
                    $newDict[$key] = null;
                }
            }
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
        foreach ($many as &$item) {
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
        $keys = array_map(function ($key) use ($prefix) {
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
        $keys = array_map(function ($key) use ($suffix) {
            return $key . $suffix;
        }, array_keys($arr));

        return array_combine($keys, array_values($arr));
    }

    /**
     * filters assoc array
     *
     * was used by cloudlister
     * 05/2021 moved from UtilArray::filterArrayByKeys() to UtilDict::filterByKeys()
     * 05/2021 unused (TODO? remove)
     *
     *
     * @param array $arr
     * @param string[] $allowedKeys
     * @return array
     */
    public static function filterByKeys(array $arr, array $allowedKeys)
    {
        $new = [];
        foreach ($arr as $key => &$val) {
            if (in_array($key, $allowedKeys, true)) {
                $new[$key] = $val;
            }
        }

        return $new;
    }

    /**
     * Filters dict ("assoc array") by its keys and convert it to a list ("numeric array")
     *
     * example:
     *
     * UtilDict::extractValues(['aaa' => 123, 'bbb' => 456], ['bbb', 'ccc']) -->
     *
     * array:2 [
     *   0 => 456
     *   1 => null
     * ]
     *
     *
     * 09/2017 from scrapers
     * 12/2019 merged UtilArray::filterByKey and UtilArray::extractByKeys to this
     * 05/2021 moved from UtilCsv::dictToList to UtilDict::toList()
     * 07/2022 renamed from toList() to extractValues()
     * 02/2023 renamed from extractValues() to extractValuesToList()
     * 02/2024 renmaed from extractValuesToList() to pickToList()
     *
     * used by cloudlister(exportOrdersToExcel), cm
     *
     * @param array $dict
     * @param string[] $keys
     * @return array numeric(!) array
     */
    public static function pickToList(array $dict, array $keys): array
    {
        return array_map(function ($key) use ($dict) {
            return $dict[$key] ?? null;
        }, $keys);
    }

    /**
     * 07/2022 created, used by MB
     * converts a dict to list of assoc arrays , eg:
     *      IN: {a:1, b:2}
     *      OUT: [{key:"a", value:1}, {key:"b", value:2}]
     *
     * @param array $dict
     * @return array list of assoc arrays
     */
    public static function toDictArray(array $dict, string $keyName = 'key', string $valueName = 'value'): array
    {
        $ret = [];
        foreach ($dict as $k => $v) {
            $ret[] = [
                $keyName   => $k,
                $valueName => $v,
            ];
        }

        return $ret;
    }



    /**
     * 05/2021 created push4
     *
     * @param array $dict
     * @param string $path
     * @param bool $bExceptionOnNotFound
     * @return mixed the value at give path, if found; null otherwise (or Exception is thrown if bExceptionOnNotFound)
     * @throws \Exception
     */
    public static function deepGet(array $dict, string $path, bool $bExceptionOnNotFound = true)
    {
        $subfields = explode('.', $path);

        foreach ($subfields as $fieldName) {
            if ($dict === null || !array_key_exists($fieldName, $dict)) {
                if ($bExceptionOnNotFound) {
                    throw new \Exception("path '$path' does not exist");
                } else {
                    return null;
                }
            }
            $dict = &$dict[$fieldName];
        }

        return $dict;
    }


    /**
     * 07/2021 created, used by ct, mb
     * 11/2021 support for dot-notation paths added
     * 11/2021 parameter bExceptionOnNotFound added
     * 09/2022 bugfixed
     *
     * @param array $dict the dict aka assoc array
     * @param string[] $keysToDelete
     */
    public static function unsetMany(array &$dict, array $keysToDelete, bool $bExceptionOnNotFound = false)
    {
        foreach ($keysToDelete as $key) {
            $dict2 = &$dict;
            // UtilDebug::d($dict2);
            $subfields = explode('.', $key);
            foreach ($subfields as $idx => $fieldName) {
                if (!array_key_exists($fieldName, $dict2)) {
                    if ($bExceptionOnNotFound) {
                        throw new \Exception("path '$key' does not exist");
                    } else {
                        break;
                    }
                }
                // UtilDebug::d($key, $fieldName);
                if($idx === count($subfields) -1) {
                    // UtilDebug::d($dict2[$key]);
                    unset($dict2[$fieldName]);
                } else {
                    $dict2 = &$dict2[$fieldName];
                }
            }
            // UtilDebug::d($dict2, $key);
        }
    }

    /**
     * renames keys of a dict
     *
     * 09/2022 created (MB)
     * 11/2023 bugfix: skip if oldKey === newKey
     *
     * @param array $dict assoc array
     * @param array $renames assoc array
     * @param bool $bExceptionOnNotFound
     */
    public static function renameKeys(array &$dict, array $renames, bool $bExceptionOnNotFound = false) {
        foreach($renames as $oldKey => $newKey) {
            if($oldKey === $newKey) {
                continue;
            }
            if(!array_key_exists($oldKey, $dict) && $bExceptionOnNotFound) {
                throw new \Exception("key fail: $oldKey");
            }
            $dict[$newKey] = $dict[$oldKey] ?? null;
            unset($dict[$oldKey]);
        }
    }

    /**
     * adds an entry at a specific position to a dict
     *
     * 08/2023 created
     *
     * @param array $dict
     * @param int $pos
     * @param $key
     * @param $value
     * @return void
     */
    public static function insertAt(array &$dict, int $pos, $key, $value): void
    {
        $dict = array_slice($dict, 0, $pos, true) +
            [$key => $value] +
            array_slice($dict, $pos, count($dict) - $pos, true);
    }

    /**
     * 11/2023 created
     *
     * @param array $origDict assoc array
     * @param array $renames assoc array
     * @return array picked data with renamed keys
     */
    public static function pickAndRenameKeys(array $origDict, array $renames): array
    {
        $picked = self::pick($origDict, array_keys($renames));
        self::renameKeys($picked, $renames);

        return $picked;
    }


}

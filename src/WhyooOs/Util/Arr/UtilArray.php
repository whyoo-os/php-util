<?php

namespace WhyooOs\Util\Arr;

use WhyooOs\Util\UtilAssert;

/**
 *
 */
class UtilArray
{



    /**
     * removes all occurrences of $toRemove from $arr
     * 04/2021 TODO: rename withoutOne
     *
     * @param array $arr
     * @param mixed $toRemove
     * @return array $arr without $toRemove
     */
    public static function without(array $arr, $toRemove)
    {
        return array_diff($arr, [$toRemove]);
    }

    /**
     * removes all occurrences of elements of $toRemove from $arr
     * 04/2021 created
     *
     * @param array $arr
     * @param mixed[] $toRemove
     * @return array $arr without $toRemove
     */
    public static function withoutMany(array $arr, array $toRemove)
    {
        return array_diff($arr, $toRemove);
    }




    /**
     * 12/2017
     * basically a convenience wrapper array array_map
     *
     * @param array $arr
     * @param $keyName
     * @param bool $bKeepOriginalKeys
     * @return array
     */
    public static function getAssocProperty(array $arr, $keyName, $bKeepOriginalKeys = false)
    {
        if ($bKeepOriginalKeys) {
            // keep original keys
            array_walk($arr, function (&$item, $key) use ($keyName) {
                $item = $item[$keyName];
            });
            $newArray = $arr;
        } else {
            // new keys (create ordinary numeric array)
            $newArray = array_map(function ($item) use ($keyName) {
                return $item[$keyName];
            }, $arr);
        }

        return $newArray;
    }



    /**
     * @param array $arr
     * @return bool
     */
    public static function isAssoc(array $arr)
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
        // alternative method:
        // return (bool)count(array_filter(array_keys($array), 'is_string'));
    }

    /**
     * @param array $arr
     * @return bool
     */
    public static function isNumeric(array $arr)
    {
        return array_keys($arr) === range(0, count($arr) - 1);
    }

    /**
     * 06/2020
     *
     * @param array $arr
     * @return bool
     */
    public static function isNumericArray($arr)
    {
        return is_array($arr) && self::isNumeric($arr);
    }

    /**
     * 06/2020
     *
     * @param array $arr
     * @return bool
     */
    public static function isAssocArray($arr)
    {
        return is_array($arr) && self::isAssoc($arr);
    }


    public static function toOneDimensionalArray(array $array)
    {
        $it = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($array));
        $oneDim = iterator_to_array($it, false);

        return $oneDim;
    }



    /**
     * 07/2018
     * used by Schlegel
     *
     * @param array $arr
     * @param string $key
     * @return array
     */
    public static function groupByKey(array $arr, string $key)
    {
        $ret = [];
        foreach ($arr as &$item) {
            $ret[$item[$key]][] = $item;
        }

        return $ret;
    }


    /**
     * 10/2020
     * used by CloudLister
     *
     * @param array $arr
     * @param string $key
     * @return array
     */
    public static function groupByGetter(array $arr, string $getterName)
    {
        $ret = [];
        foreach ($arr as &$item) {
            $ret[$item->$getterName()][] = $item;
        }

        return $ret;
    }


    /**
     * 08/2022 TODO: move to UtilDictArray/UtilObjectArray/UtilDocumemtArray
     * 08/2022 TODO where is this used?
     *
     * @param array $arr
     * @param $key
     * @param $value
     * @return int|string|null index/key
     */
    public static function findIndex(array $arr, $key, $value)
    {
        foreach ($arr as $idx => $item) {
            if (is_object($item) && $item->$key == $value) {
                return $idx;
            }
            if (is_array($item) && $item[$key] == $value) {
                return $idx;
            }
        }

        return null;
    }


    /**
     * @param array $arr
     * @return mixed
     */
    public static function getLastKey(array &$arr)
    {
        return key(array_slice($arr, -1, 1, TRUE));
    }


    /**
     * helper to iterate over arrays (ring buffer like)
     * acts as circular buffer
     * used in fixtures
     *
     * @param $array
     * @return mixed
     * @throws AssertException
     */
    public static function getNext(array &$array)
    {
        UtilAssert::assertNotEmpty($array);

        if(key($array) === null) {
            // If the internal pointer points beyond the end of the elements list or the array is empty, key() returns NULL.
            reset($array);
        }
        $el = current($array);
        if (key($array) == self::getLastKey($array)) {
            reset($array);
        } else {
            next($array);
        }

        return $el;
    }

    /**
     * @param array $arr
     * @return array
     */
    public static function removeEmptyElements(array $arr)
    {
        foreach ($arr as $idx => &$a) {
            if (empty($a)) {
                unset($arr[$idx]);
            }
        }

        return $arr;
    }

    /**
     * @param array $values array with unquoted strings
     * @param string $delimiter
     * @return array array with quoted strings
     */
    public static function pregQuoteArray(array $values, string $delimiter = '/')
    {
        return array_map(function ($val) use ($delimiter) {
            return preg_quote($val, $delimiter);
        }, $values);
    }





    /**
     * from marketer v1
     * TODO: remove
     *
     * @param array $arr
     * @return object[]
     */
    public static function arrayToObject(array $arr)
    {
        return array_map(function ($x) {
            return (object)$x;
        }, $arr);
    }

    /**
     * from marketer v1
     * TODO: remove
     *
     *
     * @param array $arr
     * @return array[]
     */
    public static function objectToArray(array $arr)
    {
        return array_map(function ($x) {
            return (array)$x;
        }, $arr);
    }


    /**
     * 05/2018 marketer
     *
     * @param $arr
     * @param $element
     */
    public static function addElementUnique(&$arr, $element)
    {
        if (!in_array($element, $arr, true)) {
            $arr[] = $element;
        }
    }

    /**
     * 07/2017 schlegel
     * 05/2018 marketer
     * 04/2020 bugfixed (pypush4 graphgen)
     *
     * @param $arr
     * @param $needle
     */
    public static function removeElement(&$arr, $needle)
    {
        $key = array_search($needle, $arr, true);
        if ($key !== false) {
            unset($arr[$key]);
        }
    }


    /**
     * 11/2017 push4
     * uses array_splice instead of unset .. thus the array must be numeric assay
     *
     * @param $arr
     * @param $needle
     */
    public static function removeElementFromNumericArray(&$arr, $needle)
    {
        $idx = array_search($needle, $arr, true);

        if ($idx !== false) {
            UtilAssert::assertIsInt($idx);
            array_splice($arr, $idx, 1);
        }
    }


    /**
     * 01/2018 moved from UtilMongo to here
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|\Doctrine\ODM\MongoDB\Cursor|\MongoCursor|\Iterator|array $arr
     * @return array
     */
    public static function iteratorToArray($arr, $useKeys = true)
    {
        if (is_array($arr)) {
            return $arr;
        }

        return iterator_to_array($arr, $useKeys);
    }


    /**
     * 03/2018 used by cloudlister
     *
     * @param $arr
     * @return bool
     */
    public static function isEmpty($arr)
    {
        foreach ($arr as &$val) {
            if (!empty($val)) {
                return false;
            }
        }

        return true;
    }

    /**
     * 05/2018
     * used in cloudlister
     * @param $key
     */
    public static function pull(array &$arr, $key, bool $bThrowException = false)
    {
        if (array_key_exists($key, $arr)) {
            $ret = $arr[$key];
            unset($arr[$key]);
        } else {
            if ($bThrowException) {
                throw new \Exception("$key not found");
            }
            $ret = null;
        }

        return $ret;
    }




    /**
     * 08/2018 used for UtilAssert
     *
     * @param array $array
     * @return bool
     */
    public static function hasDuplicates(array $array)
    {
        return count($array) > count(array_flip($array));
    }


    /**
     * 08/2018 used for UtilAssert
     *
     * @param array $array
     * @return bool
     */
    public static function getDuplicates(array $array)
    {
        return array_values(array_unique(array_diff_assoc($array, array_unique($array))));
    }


    /**
     * 07/2018 used by Schlegel
     *
     * @param $array
     * @param $idx1
     * @param $idx2
     */
    public static function swapElements(array &$array, $idx1, $idx2)
    {
        [$array[$idx1], $array[$idx2]] = [$array[$idx2], $array[$idx1]];
    }


    /**
     * 07/2018 used by Schlegel
     *
     * @param array $array
     * @param int $maxElements
     * @return array
     */
    public static function cut(array $array, int $maxElements)
    {
        if (count($array) > $maxElements) {
            return array_slice($array, 0, $maxElements);
        } else {
            return $array;
        }
    }


    /**
     * calls a method with (optional) parameters, returns results as array
     *
     * 08/2018 created for marketer
     *
     * @param array $arr
     * @param $methodName
     * .. possible more arguments are passed to called method
     */
    public static function callMethod(array $arr, $methodName)
    {
        $params = array_slice(func_get_args(), 2); // skip first 2 passed params
        $res = [];
        foreach ($arr as $item) {
            $res[] = call_user_func_array([$item, $methodName], $params);
        }

        return $res;
    }


    /**
     * filters array of strings by searchterm
     * 10/2018 used by cloudlister
     *
     * @param array $arr
     * @param string $searchterm
     * @return array the filtered array
     */
    public static function filterBySearchterm(array $arr, string $searchterm, $bKeepOriginalKeys = false)
    {
        $ret = array_filter($arr, function ($val) use ($searchterm) {
            return stripos($val, $searchterm, 0) !== FALSE;
        });

        if (!$bKeepOriginalKeys) {
            $ret = array_values($ret);
        }

        return $ret;
    }


    /**
     * 12/2018 used for ImageCompositor
     *
     * @param $m
     * @param $n
     * @param $value
     * @return array 2d array
     */
    public static function declare2d($m, $n, $value = null)
    {
        return array_fill(0, $m, array_fill(0, $n, $value));
    }


}

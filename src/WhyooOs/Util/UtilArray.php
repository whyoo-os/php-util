<?php

namespace WhyooOs\Util;

/**
 *
 */
class UtilArray
{

    /**
     * explodes and trims results .. excludes empty items ...
     * example:
     * "a, b, c, ,d" returns [a,b,c,d]
     *
     * @param $delimiter
     * @param $string
     * @return array
     */
    static public function trimExplode($delimiter, $string)
    {
        $chunksArr = explode($delimiter, $string);
        $newChunksArr = [];
        foreach ($chunksArr as $value) {
            if (strcmp('', trim($value))) {
                $newChunksArr[] = trim($value);
            }
        }
        reset($newChunksArr);

        return $newChunksArr;
    }


    /**
     * 09/2017 for scrapers
     *
     * trims all entries of 1d array
     *
     * @param $arr
     * @return mixed
     */
    public static function trimArray($arr, $bRemoveEmpty = false)
    {
        foreach ($arr as $key => &$v) {
            $v = trim($v);
            if ($bRemoveEmpty && empty($v)) {
                unset($arr[$key]); // todo? use splice here?
            }
        }
        return $arr;
    }



    /**
     * removes all occurrences of $toRemove from $arr
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
     * TODO: belongs to UtilDocument
     * search for object by attribute
     *
     * @param $arr
     * @param $attributeName
     * @param $attributeValue
     * @return object|null
     */
    public static function searchObjectByAttribute($arr, string $attributeName, $attributeValue)
    {
        if( empty($arr)) {
            return null;
        }

        foreach ($arr as &$obj) {
            $getter = "get" . ucfirst($attributeName);
            if ($obj->$getter() == $attributeValue) {
                return $obj;
            }
        }
        return null;
    }


    /**
     * @param $arr
     * @param string $attributeName
     * @param array $attributeValues
     */
    public static function moveElementsToBeginning(array $arr, $attributeName, array $attributeValues)
    {
        $new = [];
        foreach ($attributeValues as $val) {
            $newElem = self::searchObjectByAttribute($arr, $attributeName, $val);
            if ($newElem) {
                $new[] = $newElem;
            }
        }
        $new = array_merge($new, array_diff($arr, $new));

        return $new;
    }


    /**
     * get single property of documents in an array using getters
     * example:
     * $posts = {'a' => post1, 'b' => post2, 'c' => post3]
     * getObjectProperty($posts, 'id', false) returns [1,2,3]
     * getObjectProperty($posts, 'id', true) returns { a:1, b:2, c:3 }
     * TODO: rename getDocumentProperty
     * used in marketer 
     * @param array $arr
     * @param $propertyName
     * @param bool $keepOriginalKeys the thing with $keepOriginalKeys` is if array is associative to keep the old keys not to cretae new numeric array
     * @return array
     */
    public static function getObjectProperty(array $arr, $propertyName, $keepOriginalKeys = false)
    {
        $methodName = "get" . ucfirst($propertyName);
        if ($keepOriginalKeys) {
            // keep original keys
            array_walk($arr, function (&$item, $key) use ($methodName) {
                $item = $item->$methodName();
            });
            $newArray = $arr;
        } else {
            // new keys (create ordinary numeric array)
            $newArray = array_map(function ($item) use ($methodName) {
                return $item->$methodName();
            }, $arr);
        }

        return $newArray;
    }


    /**
     * 12/2017
     * basically a convenience wrapper array array_map
     *
     * @param array $arr
     * @param $keyName
     * @param bool $keepOriginalKeys
     * @return array
     */
    public static function getAssocProperty(array $arr, $keyName, $keepOriginalKeys = false)
    {
        if ($keepOriginalKeys) {
            // keep original keys
            array_walk($arr, function (&$item, $key) use ($methodName) {
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
     * sorts an array of assoc arrays (= a table) by a column $keyName
     *
     * @param $array
     * @param $keyName
     * @return mixed
     */
    public static function sortArrayOfArrays(&$array, $keyName, $sortOrder = SORT_ASC)
    {
        $sortArray = [];
        foreach ($array as $idx => $row) {
            $sortArray[$idx] = $row[$keyName];
        }
        array_multisort($sortArray, $sortOrder, $array);

        return $array;
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


    public static function toOneDimensionalArray(array $array)
    {
        $it = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($array));
        $oneDim = iterator_to_array($it, false);

        return $oneDim;
    }

    /**
     * todo: belongs to UtilRandom
     *
     * @param $array
     * @param $count
     * @return array
     */
    public static function getRandomElements($array, $count)
    {
        if ($count > count($array)) {
            $count = count($array);
        }
        $indexes = array_rand($array, $count);

        if ($count == 1) { // force array
            $indexes = [$indexes];
        }
        $randomArray = [];
        foreach ($indexes as $index) {
            $randomArray[] = $array[$index];
        }

        return $randomArray;
    }

    /**
     * todo: belongs to UtilRandom
     *
     * @param $array
     * @return mixed
     */
    public static function getRandomElement($array)
    {
        return $array[array_rand($array)];
    }


    /**
     * aka numeric2assoc
     *
     * @param $array
     * @param $keyName
     * @return array
     */
    public static function arrayOfArraysToAssoc($array, $keyName)
    {
        $values = array_values($array);
        $keys = array_column($values, $keyName);

        return array_combine($keys, $values);
    }



    /**
     * @param $array
     * @param $keyName
     * @return array
     */
    public static function arrayOfDocumentsToAssoc($array, $keyName='id')
    {
        $values = array_values($array);
        $keys = [];
        foreach ($values as &$doc) {
            $getter = "get" . ucfirst($keyName);
            $keys[] = $doc->$getter();
        }

        return array_combine($keys, $values);
    }


    /**
     * @param array $arr
     * @param $key
     * @param $value
     * @return mixed|null
     */
    public static function findOne(array $arr, $key, $value)
    {
        foreach ($arr as $item) {
            if (is_object($item) && $item->$key == $value) {
                return $item;
            }
            if (is_array($item) && $item[$key] == $value) {
                return $item;
            }
        }

        return null;
    }

    /**
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
     * @param $arr
     * @return mixed
     */
    public static function getNext(&$arr)
    {
        $el = current($arr);
        if (key($arr) == self::getLastKey($arr)) {
            reset($arr);
        } else {
            next($arr);
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




//
//    /**
//     * @param $array
//     * @param $keyName
////     */
//    public static function arrayOfArraysToAssoc( $array, $keyName)
//    {
//        $values = array_values($array);
//        $keys = array_column($values, $keyName);
//
//        return array_combine($keys, $values);
//    }
//
//    /**
//     * @param $array
//     * @param $keyName
////     */
//    public static function arrayOfDocumentsToAssoc( $array, $keyName)
//    {
//        $values = array_values($array);
//        $keys = [];
//        foreach($values as $doc) {
//            $getter = "get".ucfirst($keyName);
//            $keys[] = $doc->$getter();
//        }
//
//        return array_combine($keys, $values);
//    }


    /**
     * todo: merge with filterByKey ?
     *
     * filters assoc array
     * used by ebayGen
     *
     * @param array $arr
     * @param array $allowedKeys
     * @return array
     */
    public static function filterArrayByKeys(array $arr, array $allowedKeys)
    {
        $new = [];
        foreach ($arr as $key => &$val) {
            if (in_array($key, $allowedKeys)) {
                $new[$key] = $val;
            }
        }
        return $new;
    }

    /**
     * todo: merge with filterArrayByKeys ?
     * Filter array by its keys using a callback.
     * @return array numeric(!) array
     */
    public static function filterByKey(array $arr, $keys)
    {
        return array_map(function ($key) use ($arr) {
            return $arr[$key];
        }, $keys);
    }



    /**
     * 09/2017 from scrapers
     *
     * @param array $hash dict
     * @param array $keys
     * @return array (numeric array / list)
     */
    public static function extractByKeys(array $hash, array $keys)
    {
        $ret = [];
        foreach ($keys as $key) {
            $ret[] = @$hash[$key];
        }

        return $ret;
    }


    // from marketer v1
    public static function arrayToObject(array $arr)
    {
        return array_map(function ($x) {
            return (object)$x;
        }, $arr);
    }

    // from marketer v1
    public static function objectToArray(array $arr)
    {
        return array_map(function ($x) {
            return (array)$x;
        }, $arr);
    }


    /**
     * 07/2017 schlegel
     *
     * @param $arr
     * @param $needle
     */
    public static function removeElement(&$arr, $needle)
    {
        $key = array_search($needle, $arr);

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
        $idx = array_search($needle, $arr);

        if ($idx !== false) {
            UtilAssert::assertIsInt($idx);
            array_splice($arr, $idx, 1);
        }
    }


}

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
     * source: http://php.net/manual/en/function.explode.php#111307
     * 04/2018
     *
     * @param array $delimiters
     * @param $string
     * @return array
     */
    static function multiExplode(array $delimiters, $string)
    {
        $ready = str_replace($delimiters, $delimiters[0], $string);
        $launch = explode($delimiters[0], $ready);

        return $launch;
    }


    /**
     * 04/2018
     *
     * @param $delimiters
     * @param $string
     * @return array
     */
    static function trimMultiExplode(array $delimiters, $string)
    {
        $ready = str_replace($delimiters, $delimiters[0], $string);

        return self::trimExplode($delimiters[0], $ready);
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
        if (empty($arr)) {
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
     * TODO: rename to getObjectAttribute()
     * 08/2018 also sorts by sub-documents like 'userProfile.birthday'
     * example:
     * $posts = {'a' => post1, 'b' => post2, 'c' => post3]
     * getObjectProperty($posts, 'id', false) returns [1,2,3]
     * getObjectProperty($posts, 'id', true) returns { a:1, b:2, c:3 }
     * TODO: rename getDocumentProperty?
     * used in marketer
     *
     * @param array $arr
     * @param string $propertyName also sub-documents like 'userProfile.birthday' are possible
     * @param bool $bKeepOriginalKeys the thing with $bKeepOriginalKeys` is if array is associative to keep the old keys not to cretae new numeric array
     * @return array
     */
    public static function getObjectProperty(array $arr, $propertyName, $bKeepOriginalKeys = false)
    {
        $subfields = explode('.', $propertyName);

        $getterNames = [];
        foreach ($subfields as $subfield) {
            $getterNames[] = 'get' . ucfirst($subfield);
        }


        // $methodName = "get" . ucfirst($propertyName);

        if ($bKeepOriginalKeys) {
            // keep original keys
            array_walk($arr, function (&$item, $key) use ($getterNames) {
                foreach ($getterNames as $getterName) {
                    $item = $item->$getterName();
                }
            });
            $newArray = $arr;
        } else {
            // new keys (create ordinary numeric array)
            $newArray = array_map(function ($item) use ($getterNames) {
                foreach ($getterNames as $getterName) {
                    $item = $item->$getterName();
                }
                return $item;
            }, $arr);
        }

        return $newArray;
    }

    /**
     * TODO: rename getDocumentProperties?
     * used in eqipoo
     * @param array $arr
     * @param $propertyNames
     * @return array
     */
    public static function getObjectProperties(array $arr, array $propertyNames)
    {
        $methodNames = [];
        foreach ($propertyNames as $propertyName) {
            $methodNames[$propertyName] = "get" . ucfirst($propertyName);
        }
        // new keys (create ordinary numeric array)
        $newArray = array_map(function ($item) use ($methodNames) {
            $ret = [];
            foreach ($methodNames as $key => $getterName) {
                $ret[$key] = $item->$getterName();
            }
            return $ret;
        }, $arr);

        return $newArray;
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
     * sorts an array of assoc arrays (= a table) by a column $keyName
     *
     * @param array &$array
     * @param $keyName
     * @return array
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
     * sorts an array of objects
     * used by marketer (for sorting list of participants by conversationRole)
     * TODO: maybe there is faster version with a callback
     *
     * 03/2018
     * 08/2018 also sorts by sub-documents like 'userProfile.birthday'
     *
     * @param array &$array
     * @param string $attributeName eg "conversationRole", "userProfile.birthday"
     * @param int $sortOrder
     * @return array
     */
    public static function sortArrayOfObjects(&$array, $attributeName, $sortOrder = SORT_ASC)
    {
        $sortArray = self::getObjectProperty($array, $attributeName);
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
    public static function arrayOfDocumentsToAssoc($array, $keyName = 'id')
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
     * 07/2018 signature changed: replaced $key, $value with $criteria
     *
     * @param array $arr
     * @param array $criteria
     * @return mixed|null
     */
    public static function findOne(array $arr, array $criteria)
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
     * used by Schlegel, untested
     *
     * @param array $arr
     * @param array $criteria
     * @return array
     */
    public static function findMany(array $arr, array $criteria)
    {
        $ret = [];
        foreach ($arr as &$item) {
            if (self::_matchCriteria($item, $criteria)) {
                $ret[] = $item;
            }
        }

        return $ret;
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
     * used by mcxlister
     *
     * @param array $arr
     * @param array $allowedKeys
     * @return array
     */
    public static function filterArrayByKeys(array $arr, array $allowedKeys)
    {
        $new = [];
        foreach ($arr as $key => &$val) {
            if (in_array($key, $allowedKeys, true)) {
                $new[$key] = $val;
            }
        }

        return $new;
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
        if($key !== false) {
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
     * 02/2018 unused
     *
     * @param string $getterName eg "getId"
     * @param array $items
     * @return array
     */
    public static function arrayColumnByGetter($getterName, $items)
    {
        return array_map(function ($f) use ($getterName) {
            return $f->$getterName();
        }, $items);
    }


    /**
     * 03/2018 used by mcxlister
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
     * used in mcxlister
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
     * 07/2018
     *
     * @param $item
     * @param $criteria
     * @return bool
     */
    private static function _matchCriteria($item, array $criteria)
    {
        $bMatch = true;

        foreach ($criteria as $key => $val) {
            if (is_array($item) && $item[$key] != $val) {
                $bMatch = false;
                break;
            }
            if (is_object($item) && $item->$key != $val) {
                $bMatch = false;
                break;
            }
        }

        return $bMatch;
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
        list($array[$idx1], $array[$idx2]) = [$array[$idx2], $array[$idx1]];
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
     * 10/2018 used by mcxlister
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

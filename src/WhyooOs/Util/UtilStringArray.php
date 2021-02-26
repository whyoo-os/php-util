<?php

namespace WhyooOs\Util;


/**
 * Utility functions to for arrays of strings
 *
 * 02/2021 created
 */
class UtilStringArray
{

    /**
     * 02/2021 created
     *
     * @param string[] $arr
     * @param string $str
     * @return string[]
     */
    public static function appendToEach(array $arr, string $str)
    {
        return array_map(function ($x) use ($str) {
            return $x . $str;
        }, $arr);
    }

    /**
     * 02/2021 created
     *
     * @param string[] $arr
     * @param string $str
     * @return string[]
     */
    public static function prependToEach(array $arr, string $str)
    {
        return array_map(function ($x) use ($str) {
            return $str . $x;
        }, $arr);
    }

    /**
     * 09/2017 for scrapers
     * 02/2021 UtilStringArray::trimEach --> UtilStringArray::trimEach
     *
     * trims all entries of 1d array
     *
     * @param $arr
     * @return mixed
     */
    public static function trimEach($arr, $bRemoveEmpty = false)
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
     * explodes and trims results .. excludes empty items ...
     * example:
     * "a, b, c, ,d" returns [a,b,c,d]
     *
     * 02/2021 moved from UtilArray::trimExplode() to UtilStringArray::trimExplode()
     * 02/2021 added parameter $limit
     *
     * @param string $delimiter
     * @param string $string
     * @param int|null $limit
     * @return array
     */
    static public function trimExplode(string $delimiter, string $string, $limit = null, $bKeepEmpty = false)
    {
        if(is_null($limit)) {
            $chunksArr = explode($delimiter, $string);
        } else {
            $chunksArr = explode($delimiter, $string, $limit);
        }

        $newChunksArr = [];
        foreach ($chunksArr as $value) {
            if (strcmp('', trim($value)) || $bKeepEmpty) {
                $newChunksArr[] = trim($value);
            }
        }
        reset($newChunksArr);

        return $newChunksArr;
    }


    /**
     * source: http://php.net/manual/en/function.explode.php#111307
     * 04/2018
     * 02/2021 moved from UtilArray::multiExplode() to UtilStringArray::multiExplode()
     * 02/2021 ??? what is the use of this???? FIXME
     *
     * @param string[] $delimiters
     * @param string $str
     * @return string[]
     */
    static function multiExplode(array $delimiters, $str)
    {
        $ready = str_replace($delimiters, $delimiters[0], $str);
        $launch = explode($delimiters[0], $ready);

        return $launch;
    }


    /**
     * 04/2018
     * 02/2021 moved from UtilArray::trimMultiExplode() to UtilStringArray::trimMultiExplode()
     * 02/2021 ??? what is the use of this???? FIXME
     *
     * @param string[] $delimiters
     * @param string $str
     * @return string[]
     */
    static function trimMultiExplode(array $delimiters, $str)
    {
        $ready = str_replace($delimiters, $delimiters[0], $str);

        return self::trimExplode($delimiters[0], $ready);
    }


    /**
     * 02/2021 created
     *
     * @param array $arr
     * @param string $needle
     * @return array|false[]|string[]
     */
    public static function removeFromBeginningEach(array $arr, string $needle)
    {
        return array_map(function ($x) use ($needle) {
            return UtilString::removeFromBeginning($x, $needle);
        }, $arr);
    }

    /**
     * 02/2021 created
     *
     * @param array $arr
     * @param string $needle
     * @return array|false[]|string[]
     */
    public static function removeFromEndEach(array $arr, string $needle)
    {
        return array_map(function ($x) use ($needle) {
            return UtilString::removeFromEnd($x, $needle);
        }, $arr);
    }

    /**
     * 02/2021 created
     *
     * @param array $arr
     * @param string $beginning
     * @param string $end
     * @return array|false[]|string[]
     */
    public static function removeFromBeginningAndEndEach(array $arr, string $beginning, string $end)
    {
        return array_map(function ($x) use ($beginning, $end) {
            return UtilString::removeFromBeginningAndEnd($x, $beginning, $end);
        }, $arr);
    }


}

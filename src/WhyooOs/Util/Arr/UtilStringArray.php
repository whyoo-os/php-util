<?php

namespace WhyooOs\Util\Arr;


use WhyooOs\Util\UtilString;

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


    /**
     * 07/2019 created
     * 02/2021 moved from cloudlister's UtilSku to UtilStringArray
     *
     * source: https://stackoverflow.com/a/1336357/2848530
     *
     * @param string[]
     * @param string $sep single character
     * @return bool|string
     */
    public static function getCommonPrefix(array $arrStrings, string $sep = '-')
    {
        if (empty($arrStrings)) {
            return '';
        }

        $pl = 0; // common prefix length
        $pl2 = 0;
        $n = count($arrStrings);
        $l = strlen($arrStrings[0]);
        while ($pl < $l) {
            $c = $arrStrings[0][$pl];
            for ($i = 1; $i < $n; $i++) {
                if ($arrStrings[$i][$pl] !== $c) {
                    break 2;
                }
            }
            $pl++;
            if ($c == $sep) {
                $pl2 = $pl;
            }
        }
        $prefix = substr($arrStrings[0], 0, $pl2);

        return $prefix;
    }


}

<?php


namespace WhyooOs\Util;


/**
 *
 */
class UtilNumber
{
    /**
     * @param float|int $number
     * @param float|int $min
     * @param float|int $max
     * @return float|int
     */
    public static function clip($number, $min, $max)
    {
        return max(min($number, $max), $min);
    }


    /**
     * replaces komma (german notation) with dot (american notation)
     * eg "100,12" --> 100.12
     * @param $string
     * @return float
     */
    public static function stringToNumber($string)
    {
        return floatval(str_replace(',', '.', $string));
    }


    /**
     * @param $number
     * @return int -1, 0 or 1
     */
    public static function getSign($number)
    {
        return (int)(($number > 0) - ($number < 0));
    }



}


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
     * separators NOT allowed
     * eg "100,12" --> 100.12
     * @param $string
     * @return float
     */
    public static function stringToNumber($string)
    {
        $string = preg_replace('/([^0-9\.,])/i', '', $string);
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


    /**
     * 02/2020
     * 
     * @param float $y
     * @param float $minY
     * @param float $maxY
     * @return float normalized y between 0 and 1
     */
    public static function normalize(float $y, float $minY, float $maxY)
    {
        return ($y - $minY) / ($maxY - $minY);
    }

}


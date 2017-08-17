<?php


namespace WhyooOs\Util;


/**
 *
 */
class UtilNumber
{
    /**
     * @param float/int $number
     * @param float/int $min
     * @param float/int $max
     * @return float/int
     */
    public static function clip($number, $min, $max)
    {
        return max(min($number, $max), $min);
    }

}


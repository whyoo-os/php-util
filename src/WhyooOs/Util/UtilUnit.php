<?php

namespace WhyooOs\Util;

use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Response;

/**
 * class for converting between different units
 *
 *       paper sizes
 *       -----------
 *       A3    297 x 420 mm
 *       A4    210 x 297 mm
 *       A5    148 x 210 mm
 *       A6    105 x 148 mm
 *
 */
class UtilUnit
{

    const ONE_INCH_IN_MM = 25.4; // 1 inch = 25.4 mm


    /**
     * @param float|int $px
     * @param float|int $dpi
     * @return float mm
     */
    public static function px2mm($px, $dpi = 96)
    {
        $onePxInMm = self::ONE_INCH_IN_MM / $dpi;

        return $onePxInMm * $px;
    }

    public static function mm2px($mm, $dpi = 96)
    {
        $onePxInMm = self::ONE_INCH_IN_MM / $dpi;

        return $mm / $onePxInMm;
    }


    /**
     * There are 72 points per inch; if it is sufficient to assume 96 pixels per inch, the formula is rather simple:
     * points = pixels * 72 / 96
     * The W3C has defined the pixel measurement px as exactly 1/96th of 1in regardless of the actual resolution of your display, so the above formula should be good for all web work.
     *
     * @param $px
     * @param int $dpi
     * @return float|int
     */
    public static function px2pt($px, $dpi = 96)
    {
        return $px * 72 / $dpi;
    }


}
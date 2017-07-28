<?php

namespace WhyooOs\Util;

use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Response;

/**
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

}
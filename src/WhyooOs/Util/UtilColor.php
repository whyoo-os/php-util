<?php


namespace WhyooOs\Util;






class UtilColor
{
    public static function lighten($hex, $percent)
    {
        return self::mix($hex, '#fff', $percent);
    }

    public static function darken($hex, $percent)
    {
        return self::mix($hex, '#000', $percent);
    }

    public static function mix($col1, $col2, $mix)
    {
        $rgb1 = self::hex2rgb($col1);
        $rgb2 = self::hex2rgb($col2);
        $ret = '#';
        for ($i = 0; $i < 3; $i++) {
            $ret .= sprintf('%02x', round($rgb1[$i] * (1.0 - $mix) + $rgb2[$i] * $mix));
        }
        return $ret;
    }

    public static function hex2rgb($col)
    {
        if (preg_match('~^#([0-f]{1})([0-f]{1})([0-f]{1})$~i', trim($col), $gr)) {
            return [hexdec($gr[1] . $gr[1]), hexdec($gr[2] . $gr[2]), hexdec($gr[3] . $gr[3])];
        } elseif (preg_match('~^#([0-f]{2})([0-f]{2})([0-f]{2})$~i', trim($col), $gr)) {
            return [hexdec($gr[1]), hexdec($gr[2]), hexdec($gr[3])];
        } else {
            throw new \Exception('invalid color ' . $col);
        }
    }


    /**
     * 07/2017 moved from UtilImage to here
     *
     * @param $strHex
     * @return mixed
     */
    public static function cssHexToInt($strHex)
    {
        $strHex = str_replace('#', '', $strHex);
        list($r, $g, $b) = sscanf($strHex, "%02x%02x%02x");

        return $r * 0x10000 + $g * 0x100 + $b;
    }



    /**
     * from marketer v1
     *
     * @param $idx
     * @return mixed|string
     */
    static function getLegendColor( $idx)
    {
        $grundfarben = [
            '#fc0',
            '#F88C1F',
            '#83AEE3',
            '#FE2A29',
            '#8DCC35',
            '#CC2DD2',
        ];

        $variations = [
            ['#fff', 0.5],
            ['#000', 0.5],
            ['#fff', 0.25],
            ['#000', 0.25],
            ['#fff', 0.75],
            ['#000', 0.75],
            // noch mehr variationen
            ['#f0f', 0.5],
            ['#0f0', 0.5],
            ['#f0f', 0.25],
            ['#0f0', 0.25],
            ['#f0f', 0.75],
            ['#0f0', 0.75],
        ];
        $grundfarbe = $grundfarben[ $idx % count( $grundfarben)];
        $variation_idx = $idx / count( $grundfarben) - 1;
        $variation_idx = $variation_idx % count( $variations); # so it never gets out of range
        $color = ($variation_idx >= 0) ? self::mix( $grundfarbe, $variations[$variation_idx][0], $variations[$variation_idx][1]) : $grundfarbe;

        return $color;
    }



}


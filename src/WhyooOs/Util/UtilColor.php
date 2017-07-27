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
            return array(hexdec($gr[1] . $gr[1]), hexdec($gr[2] . $gr[2]), hexdec($gr[3] . $gr[3]));
        } elseif (preg_match('~^#([0-f]{2})([0-f]{2})([0-f]{2})$~i', trim($col), $gr)) {
            return array(hexdec($gr[1]), hexdec($gr[2]), hexdec($gr[3]));
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


}


<?php


namespace WhyooOs\Util;


class UtilFormatter
{

    /**
     * 10/2017
     * TODO: https://github.com/danielstjules/Stringy
     * TODO: belongs into UtilMail
     * @param $array
     * @return string
     */
    public static function formatDictText($array): string
    {
        $maxLengthKey = 0;
        $maxLengthVal = 0;
        foreach ($array as $key => $val) {
            $maxLengthKey = max(mb_strlen($key), $maxLengthKey);
            $maxLengthVal = max(mb_strlen($val), $maxLengthVal);
        }


        $out = "+-" . str_pad('', $maxLengthKey, "-") . "-+-" . str_pad('', $maxLengthVal, "-") . "-+\n";
        foreach ($array as $key => $val) {
            $out .= "| " . str_pad($key, $maxLengthKey) . " | " . str_pad($val, $maxLengthVal) . " |\n";
        }
        $out .= "+-" . str_pad('', $maxLengthKey, "-") . "-+-" . str_pad('', $maxLengthVal, "-") . "-+\n";

        return $out;
    }


    /**
     * 10/2017
     * used in shipping_platform
     * TODO: belongs into UtilMail
     *
     * @param $array
     * @param $keys
     * @return string
     */
    public static function formatDictHtml($array, $keys, array $translations = []): string
    {
        $out = "<table>\n";
        foreach ($keys as $key) {
            $val = htmlentities($array[$key]);
            if (array_key_exists($key, $translations)) {
                $key = $translations[$key];
            }
            $out .= "<tr><td>$key&nbsp;&nbsp;&nbsp;&nbsp;</td><td>$val</td></tr>\n";
        }
        $out .= "</table>\n";

        return $out;
    }



    /**
     * 10/2017 from scrapers
     *
     * @param int $sizeInBytes
     * @param int $precision
     * @return string
     */
    public static function formatBytes(int $sizeInBytes, int $precision = 2): string
    {
        $base = log($sizeInBytes, 1024);
        $suffixes = ['B', 'KB', 'MB', 'GB', 'TB'];

        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[(int)floor($base)];
    }

    /**
     * 02/2023 used by calculation-manager
     */
    public static function formatDuration(int|float $seconds): string
    {
        return sprintf('%02d:%02d:%02d', ($seconds/ 3600),($seconds/ 60 % 60), $seconds% 60);
    }



}
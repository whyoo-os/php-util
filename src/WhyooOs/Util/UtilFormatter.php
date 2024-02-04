<?php


namespace WhyooOs\Util;


class UtilFormatter
{

    /**
     * 10/2017 created
     * it returns a table with two columns, one for the keys, one for the values
     *
     * TODO: https://github.com/danielstjules/Stringy
     * TODO: belongs into UtilMail?
     * TODO: give it a better name
     *
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
     * 10/2017 created
     *
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
        $suffixes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

        return number_format(round(pow(1024, $base - floor($base)), $precision), $precision) . ' ' . $suffixes[(int)floor($base)];
    }


    /**
     * 02/2023 used by calculation-manager
     *
     * @param int|float $seconds
     * @param int $thresholdNoDecimals if seconds are passed as float, then values > thresholdNoDecimals will be rounded to int
     * @return string
     */
    public static function formatDuration($seconds, int $thresholdNoDecimals = 60): string
    {
        // ---- to integer if $seconds > $thresholdNoDecimals
        if (is_float($seconds) && $seconds > $thresholdNoDecimals) {
            $seconds = (int)round($seconds);
        }

        // ---- format integer seconds
        if (is_integer($seconds)) {
            return sprintf('%02d:%02d:%02d', intdiv($seconds, 3600), intdiv($seconds, 60) % 60, $seconds % 60);
        }

        // ---- format float seconds - 2 decimals for the seconds
        $X = 100;
        $secondsTimeX = (int)round($seconds * $X);
        $seconds = (int)round($seconds);

        return sprintf('%02d:%02d:%05.2f', intdiv($seconds, 3600), intdiv($seconds, 60) % 60, ($secondsTimeX % (60 * $X)) / $X);
    }

    /**
     * 02/2024 created (cm)
     */
    public static function formatInteger(int $int, $thousandsSeparator = '.'): string
    {
        return number_format($int, 0, thousands_separator: $thousandsSeparator);
    }

}
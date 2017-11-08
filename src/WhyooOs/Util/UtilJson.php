<?php
/**
 * 09/2017
 */

namespace WhyooOs\Util;


class UtilJson
{

    /**
     * json_decode with error handling
     *
     * @param $json
     * @param bool $bAssoc
     * @return mixed
     * @throws \Exception
     */
    public static function jsonDecode($json, $bAssoc = false)
    {
        $result = json_decode($json, $bAssoc);
        if (json_last_error() != JSON_ERROR_NONE) {
            throw new \Exception("JSON DECODE ERROR: " . json_last_error_msg() . "!");
        }

        return $result;
    }


    /**
     * @param string $pathJsonFile
     * @param bool $bAssoc
     * @return mixed
     */
    public static function loadJsonFile(string $pathJsonFile, $bAssoc = false)
    {
        return self::jsonDecode(file_get_contents($pathJsonFile), $bAssoc);
    }


}
<?php
/**
 * 09/2017
 */

namespace WhyooOs\Util;


use Pygmentize\Pygmentize;

class UtilJson
{

    /**
     * json_decode with error handling
     *
     * 02/2021 changed bAssoc default value from false to true
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
     * 02/2021 changed bAssoc default value from false to true
     *
     * @param string $pathJsonFile
     * @param bool $bAssoc
     * @return mixed
     * @throws \Exception
     */
    public static function loadJsonFile(string $pathJsonFile, $bAssoc = true)
    {
        return self::jsonDecode(file_get_contents($pathJsonFile), $bAssoc);
    }



    /**
     * 04/2020 used by scraper.service
     * 02/2021 changed bAssoc default value from false to true
     *
     * composer require colinodell/json5
     *
     * @param string $pathJson5File
     * @param bool $bAssoc
     * @return mixed
     * @throws \Exception
     */
    public static function loadJson5File(string $pathJson5File, $bAssoc = true)
    {
        return json5_decode(file_get_contents($pathJson5File), $bAssoc);
    }


    /**
     * @param string $pathJsonFile
     * @param $data
     * @param int $options
     * @return bool|int
     * @throws \Exception
     */
    public static function saveJsonFile(string $pathJsonFile, $data, $options=JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)
    {
        $json = json_encode($data, $options);
        if (json_last_error() != JSON_ERROR_NONE) {
            throw new \Exception("JSON ENCODE ERROR: " . json_last_error_msg() . "!");
        }

        return file_put_contents($pathJsonFile, $json);
    }


    /**
     * 11/2017 push4
     *
     * @param $code
     * @return string
     */
    public static function highlightJsonForTerminal($code)
    {
        return Pygmentize::highlight($code, 'json', 'utf-8', 'terminal');
    }


}

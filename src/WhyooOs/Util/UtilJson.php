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
     * @throws \Exception
     */
    public static function loadJsonFile(string $pathJsonFile, $bAssoc = false)
    {
        return self::jsonDecode(file_get_contents($pathJsonFile), $bAssoc);
    }


    /**
     * @param string $pathJsonFile
     * @param $data
     * @param int $options
     * @return bool|int
     */
    public static function saveJsonFile(string $pathJsonFile, $data, $options=JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)
    {
        return file_put_contents($pathJsonFile, json_encode($data, $options));
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
<?php
/**
 * 09/2017
 */

namespace WhyooOs\Util;


use Pygmentize\Pygmentize;
use WhyooOs\Util\Arr\UtilStringArray;

class UtilJson
{

    /**
     * json_decode with error handling
     *
     * 04/2021 $bAssoc - changed default value from false to true
     *
     * @param $json
     * @param bool $bAssoc
     * @return mixed
     * @throws \Exception
     */
    public static function jsonDecode($json, $bAssoc = true)
    {
        $result = json_decode($json, $bAssoc);
        if (json_last_error() != JSON_ERROR_NONE) {
            throw new \Exception("JSON DECODE ERROR: " . json_last_error_msg() . "!");
        }

        return $result;
    }


    /**
     * 04/2021 $bAssoc - changed default value from false to true
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
     * 04/2021 $bAssoc - changed default value from false to true
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
     * parses whole .jsonl log file into a list-array
     * see https://jsonlines.org/
     * 05/2022 created, used by push4
     *
     * @param string $pathJsonlFile
     * @param bool $bAssoc
     * @return array
     */
    public static function loadJsonlFile(string $pathJsonlFile, $bAssoc = true): array
    {
        $lines = file($pathJsonlFile);
        // TODO? filter empty lines?

        return array_map(fn($line) => json_decode($line, $bAssoc), $lines);
    }



    /**
     * @param string $pathJsonFile
     * @param $data
     * @param int $options
     * @return bool|int
     * @throws \Exception
     */
    public static function saveJsonFile(string $pathJsonFile, $data, $options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    {
        $json = json_encode($data, $options);
        if (json_last_error() != JSON_ERROR_NONE) {
            throw new \Exception("JSON ENCODE ERROR: " . json_last_error_msg() . "!");
        }

        return file_put_contents($pathJsonFile, $json);
    }



    /**
     * save json with possibility to add a comment at the top
     *
     * 01/2022 created
     *
     * @param string $pathJsonFile
     * @param $data
     * @param string|null $comment
     * @param int|string $options
     * @return false|int
     * @throws \Exception
     */
    public static function saveJson5File(string $pathJsonFile, $data, string $comment = null, $options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
    {
        $json = json_encode($data, $options);
        if (json_last_error() != JSON_ERROR_NONE) {
            throw new \Exception("JSON ENCODE ERROR: " . json_last_error_msg() . "!");
        }

        if ($comment) {
            $commentLines = explode("\n", $comment);
            $commentWithAsterisks = implode("\n", UtilStringArray::prependToEach($commentLines, ' * '));
            $json = "/*\n{$commentWithAsterisks}\n */\n{$json}";
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

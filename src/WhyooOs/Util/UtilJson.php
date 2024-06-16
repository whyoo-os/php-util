<?php

namespace WhyooOs\Util;


use Pygmentize\Pygmentize;
use WhyooOs\Util\Arr\UtilStringArray;

/**
 * 09/2017 created
 */
class UtilJson
{


    /**
     * gron must be installed
     * @see https://github.com/tomnomnom/gron?tab=readme-ov-file#installation
     *
     * 06/2024 created
     */
    public static function gron(array $simplified): string
    {
        $pathJsonFile = '/tmp/json-' . uniqid() . '.json';
        save::saveJsonFile($pathJsonFile, $simplified);
        // run gron on the file
        $cmd = 'gron ' . $pathJsonFile;
        $output = shell_exec($cmd);
        // cleanup
        unlink($pathJsonFile);

        return $output;
    }


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
     * 10/2022 TODO: add a (memory efficient) version with a generator / yield() ... maybe a parameter?
     *
     * @param string $pathJsonlFile
     * @param bool $bAssoc
     * @return array
     */
    public static function loadJsonlFile(string $pathJsonlFile, bool $bAssoc = true): array
    {
        $lines = file($pathJsonlFile);
        // TODO? filter empty lines?

        return array_map(fn($line) => json_decode($line, $bAssoc), $lines);
    }

    /**
     * parses whole .jsonl log file using generator/yield
     * see https://jsonlines.org/
     * 10/2022 created, used by MB
     *
     * usage
     * =====
     * $generator = UtilJson::loadJsonlFileWithGenerator('data.jsonl');
     * foreach($generator as $line) {
     *     // do something
     * }
     *
     * @param string $pathJsonlFile
     * @param bool $bAssoc
     * @return \Generator
     */
    public static function loadJsonlFileWithGenerator(string $pathJsonlFile, bool $bAssoc = true): \Generator
    {
        $fp = fopen($pathJsonlFile, 'r');
        while (($line = fgets($fp)) !== false) {
            yield json_decode($line, $bAssoc);
        }
        fclose($fp);
    }


    /**
     * @param string $pathJsonFile
     * @param $data
     * @param int $options
     * @param bool $bThrowException
     * @return bool|int
     * @throws \Exception
     */
    public static function saveJsonFile(string $pathJsonFile, $data, $options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE, bool $bThrowException=true)
    {
        $json = json_encode($data, $options);
        if (json_last_error() != JSON_ERROR_NONE) {
            if($bThrowException) {
                throw new \Exception("JSON ENCODE ERROR: " . json_last_error_msg() . "!");
            }
            return false;
        }

        return file_put_contents($pathJsonFile, $json);
    }


    /**
     * writes an array to a .jsonl file
     *
     * 10/2022 created
     *
     * @param string $pathJsonlFile
     * @param array $lines
     * @param int $jsonEncodeFlags
     * @throws \Exception
     */
    public static function saveJsonlFile(string $pathJsonlFile, array $lines, $jsonEncodeFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    {
        // ---- check
        if($jsonEncodeFlags & JSON_PRETTY_PRINT) {
            throw new \LogicException("json_encode with flag JSON_PRETTY_PRINT not possible for jsonl.");
        }
        // ---- open
        $fp = fopen($pathJsonlFile, 'w');
        if($fp === false) {
            throw new \Exception("error opening $pathJsonlFile for writing");
        }
        // ---- write line-by-line
        foreach($lines as $line) {
            if(fwrite($fp, json_encode($line, $jsonEncodeFlags) . "\n") === false) {
                throw new \Exception("error writing to $pathJsonlFile. disk full?");
            }
        }
        // ---- close
        fclose($fp);
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

<?php

namespace WhyooOs\Util;


class UtilCsv
{

    /**
     * used by FixturesUtil
     *
     * @param $pathCsv
     * @param bool $bAssoc
     * @return array array of objects or assocArrays
     * @throws \Exception
     */
    public static function parseCsvFile($pathCsv, bool $bAssoc = false)
    {
        $fileHandle = fopen($pathCsv, 'r');
        $arr = [];
        while (($row = fgetcsv($fileHandle)) !== FALSE) {
            $arr[] = $row;
        }
        fclose($fileHandle);

        $headers = array_shift($arr); // remove header row
        $aObjects = [];
        foreach ($arr as $row) {
            if (count($row) == count($headers)) { // valid row
                if( $bAssoc) {
                    // assoc array
                    $aObjects[] = array_combine($headers, $row);
                } else {
                    // object
                    $aObjects[] = (object)array_combine($headers, $row);
                }
            } else {
                throw new \Exception('invalid row');
            }
        }

        return $aObjects;
    }


    /**
     * private helper for arrayToCsv
     *
     * @param $json
     * @param string $prefix
     * @param array $aIgnore
     * @param array $row
     * @return array
     */
    private static function _flatten($json, $prefix = '', $aIgnore = [], $row = [])
    {
        foreach ($json as $key => $val) {
            if (in_array($key, $aIgnore)) {
                continue;
            }
            if (is_array($val)) {
                if (UtilArray::isAssoc($val) || is_array($val[0])) {
                    $row = array_merge($row, self::_flatten($val, "$prefix$key.", $aIgnore, $row));
                } else {
                    $row[$prefix . $key] = implode("\n", $val);
                    // dump($prefix . $key, $val);
                }
            } else {
                $row[$prefix . $key] = $val;
            }
        }
        return $row;
    }




    /**
     * flattens array of rows and exports to .csv
     * 09/2017 for scrapers
     *
     * @param array $rows
     * @param array|null $columns
     * @return string csv
     */
    public static function arrayToCsv(array $rows, array $columns = null )
    {
        if (count($rows) == 0) {
            return null;
        }

        // 1) flatten each row
        foreach ($rows as &$row) {
            $row = self::_flatten($row);
        }

        // 2) find keys, sort
        $keys = [];
        foreach ($rows as &$row) {
            $keys = array_unique(array_merge($keys, array_keys($row)));
        }

        // 3) sort keys alphabetically OR filter array keys
        if( is_null($columns)) {
            asort($keys);
        } else {
            $keys = array_intersect($keys, $columns);
        }

        // 4) write header + rows
        ob_start();
        $df = fopen("php://output", 'w');
        fputcsv($df, $keys);
        foreach ($rows as $row) {
            fputcsv($df, UtilArray::extractByKeys($row, $keys));
        }
        fclose($df);

        return ob_get_clean();
    }


    /**
     * ssconvert --list-exporters
     * @param $pathCsv
     */
    public static function csvToXls($pathCsv)
    {
        $pathXls = UtilFilesystem::replaceExtension($pathCsv, 'xls');
        exec("ssconvert  $pathCsv $pathXls");
    }

    /**
     * ssconvert --list-exporters
     * @param $pathCsv
     */
    public static function csvToXlsx($pathCsv)
    {
        $pathXlsx = UtilFilesystem::replaceExtension($pathCsv, 'xlsx');
        exec("ssconvert  $pathCsv $pathXlsx");
    }


}
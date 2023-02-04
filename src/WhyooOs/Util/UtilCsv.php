<?php

namespace WhyooOs\Util;


use WhyooOs\Util\Arr\UtilArray;
use WhyooOs\Util\Arr\UtilStringArray;

class UtilCsv
{

    /**
     * used by FixturesUtil
     * 03/2021 used by algotrend
     *
     * @param string $pathCsv
     * @param bool $bAssoc
     * @param bool $bTrim
     * @param string $separator
     * @param int $skip
     * @return array array of objects or assocArrays
     * @throws \Exception
     */
    public static function parseCsvFile(string $pathCsv, bool $bAssoc = false, $bTrim = true, string $separator = ',', int $skip = 0)
    {
        $fileHandle = fopen($pathCsv, 'r');
        $arr = [];
        $rowIdx = 0;
        while (($row = fgetcsv($fileHandle, 0, $separator)) !== FALSE) {
            if ($rowIdx++ < $skip) {
                continue;
            }
            $arr[] = $bTrim ? UtilStringArray::trimEach($row) : $row;
        }
        fclose($fileHandle);

        $headers = array_shift($arr); // remove header row
        $aObjects = [];
        foreach ($arr as $row) {
            if (count($row) == count($headers)) { // valid row
                if ($bAssoc) {
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
     * used by ct
     *
     * flattens array of rows and exports to .csv
     * 09/2017 for scrapers
     * 12/2019 updated
     * 11/2021 added functionality to have custom header names if $columns is assoc array
     * 01/2022 bugfixed and sorting of extracted header column
     * 01/2023 bugfixed UtilDict::toList --> UtilDict::
     *
     * @param array $rows
     * @param array|null $columns numeric array or assoc array
     * @return string csv
     */
    public static function arrayToCsv(array $rows, array $columns = null): string
    {
        if (count($rows) == 0) {
            return null;
        }

        // 1) flatten each row
        foreach ($rows as &$row) {
            $row = self::_flatten($row);
        }

        // 2) (optional) find and sort keys alphabetically
        if (is_null($columns)) {
            $union = [];
            foreach ($rows as &$row) {
                $union += $row;
            }
            $columns = array_keys($union);
            // asort($columns);
        }

//        if(!UtilArray::isAssocArray($columns)) {
//            $columns = array_combine($columns, $columns);
//        }

        // ---- optional header names if $columns is an assoc array
        if(UtilArray::isAssocArray($columns)) {
            $headerNames = array_values($columns);
            $columnNames = array_keys($columns);
        } else {
            $headerNames = $columns;
            $columnNames = $columns;
        }

        // ---- write header
        ob_start();
        $df = fopen("php://output", 'w');
        fputcsv($df, $headerNames);

        // ---- write rows
        foreach ($rows as &$row) {
            fputcsv($df, UtilDict::extractValuesToList($row, $columnNames));
        }
        fclose($df);

        return ob_get_clean();
    }



    /**
     * see https://github.com/dagwieers/unoconv/blob/master/doc/unoconv.1.adoc
     *
     * @param string $pathCsv
     * @param string $format xls, xlsx, ods or other format
     * @return bool true on success false otherwise
     * @internal param string $format
     */
    public static function csvToExcel(string $pathCsv, string $format = 'xls')
    {
        // ---- 1st try with libreoffice's unoconv
        exec("unoconv -i FilterOptions=44,34,76 -f $format $pathCsv", $output, $return);
        if ($return == 0) {
            return true;
        }

        // ---- 2nd try: use gnumeric's ssconvert
        $pathXls = UtilFilesystem::replaceExtension($pathCsv, $format);
        exec("ssconvert  $pathCsv $pathXls", $output, $return);

        return $return == 0;
    }




}

<?php

namespace WhyooOs\Util;


use WhyooOs\Util\Arr\UtilArray;
use WhyooOs\Util\Arr\UtilStringArray;

class UtilCsv
{

    /**
     * parses a .csv file into an array of dicts
     *
     * used by FixturesUtil
     * 03/2021 used by algotrend
     * 05/2023 used by CM, parameters bTrim, bAssoc removed (breaking change)
     *
     * @param string $pathCsv
     * @param string $separator
     * @param int|null $limit
     * @param int $skip
     * @return array array of dicts
     * @throws \Exception
     */
    public static function parseCsvFile(string $pathCsv, string $separator = ',', ?int $limit=null, int $skip = 0)
    {
        $fileHandle = fopen($pathCsv, 'r');
        $arr = [];
        $rowIdx = 0;
        $headers = fgetcsv($fileHandle, 0, $separator);
        if(!$headers) {
            throw new \Exception('invalid csv file');
        }

        while (($row = fgetcsv($fileHandle, 0, $separator)) !== false) {
            // ---- skip
            if ($rowIdx < $skip) {
                $rowIdx++;
                continue;
            }
            // ---- limit
            if(!is_null($limit) && $rowIdx > $limit) {
                break;
            }
            $arr[] = UtilStringArray::trimEach($row);
            $rowIdx++;
        }
        fclose($fileHandle);

        $ret = [];
        foreach ($arr as $row) {
            if (count($row) != count($headers)) { // valid row
                throw new \Exception('invalid row');
            }

            $ret[] = array_combine($headers, $row);
        }

        return $ret;
    }


    /**
     * writes the data $rows to $fh
     * internal helper
     *
     * @param \resource $fh file handle
     * @param array $rows
     * @param array|null $columns
     * @return bool true on success, false on error
     */
    private static function _arrayToCsv($fh, array $rows, array $columns = null): bool
    {
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
        $ret = fputcsv($fh, $headerNames);
        if($ret === false) {
            return false;
        }

        // ---- write rows
        foreach ($rows as &$row) {
            $ret = fputcsv($fh, UtilDict::pickToList($row, $columnNames));
            if($ret === false) {
                return false;
            }
        }

        return true;
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
     * 01/2023 bugfixed UtilDict::toList --> UtilDict::extractValuesToList
     * 02/2023 renamed arrayToCsv --> arrayToCsvString
     *
     * @param array $rows
     * @param array|null $columns numeric array or assoc array
     * @return string csv
     */
    public static function arrayToCsvString(array $rows, array $columns = null): string
    {
        // ---- write csv to stdout and capture the output
        ob_start();
        $fh = fopen("php://output", 'w');
        self::_arrayToCsv($fh, $rows, $columns);
        fclose($fh);

        return ob_get_clean();
    }

    /**
     * 02/2023 created (used by t2-export-2023)
     *
     * @param string $pathDestCsvFile
     * @param array $rows
     * @param array|null $columns
     * @return bool true on success, false on failure
     */
    public static function arrayToCsvFile(string $pathDestCsvFile, array $rows, array $columns = null): bool
    {
        $fh = fopen($pathDestCsvFile, 'wb');
        if($fh === false) {
            return false;
        }
        $bSuccess1 = self::_arrayToCsv($fh, $rows, $columns);
        $bSuccess2 = fclose($fh);

        return $bSuccess1 && $bSuccess2;
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

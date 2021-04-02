<?php

namespace WhyooOs\Util;


use WhyooOs\Util\List\UtilStringArray;

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
     * flattens array of rows and exports to .csv
     * 09/2017 for scrapers
     * 12/2019 updated
     *
     * @param array $rows
     * @param array|null $columns
     * @return string csv
     */
    public static function arrayToCsv(array $rows, array $columns = null)
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
            asort($columns);
        }

        // 3) write header + rows
        ob_start();
        $df = fopen("php://output", 'w');
        fputcsv($df, $columns);
        foreach ($rows as &$row) {
            fputcsv($df, self::dictToList($row, $columns));
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


    /**
     * Filters dict ("assoc array") by its keys and convert it to a list ("numeric array")
     *
     * example:
     *
     * UtilCsv::dictToList(['aaa' => 123, 'bbb' => 456], ['bbb', 'ccc']) -->
     *
     * array:2 [
     *   0 => 456
     *   1 => null
     * ]
     *
     *
     * 09/2017 from scrapers
     * 12/2019 merged UtilArray::filterByKey and UtilArray::extractByKeys to this
     * @return array numeric(!) array
     */
    public static function dictToList(array $dict, array $keys)
    {
        return array_map(function ($key) use ($dict) {
            return @$dict[$key];
        }, $keys);

//        # alternative implementation...
//        $ret = [];
//        foreach ($keys as $key) {
//            $ret[] = @$hash[$key];
//        }
//        return $ret;
    }


}

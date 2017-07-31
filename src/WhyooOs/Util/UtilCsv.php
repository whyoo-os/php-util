<?php

namespace WhyooOs\Util;


class UtilCsv
{

    /**
     * used by FixturesUtil
     *
     * @param $pathCsv
     * @return array
     */
    public static function parseCsvFileToObjects($pathCsv)
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
                $aObjects[] = (object)array_combine($headers, $row);
            }
        }

        return $aObjects;
    }


}
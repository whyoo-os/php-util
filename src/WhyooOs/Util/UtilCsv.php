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


}
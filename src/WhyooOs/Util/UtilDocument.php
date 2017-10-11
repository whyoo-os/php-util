<?php


namespace WhyooOs\Util;



class UtilDocument
{


    /**
     * used by ebaygen5
     *
     * can also handle field names like inventoryItem.currentStockLevel for embedded stuff
     *
     * @param $document the document with getters and setters
     * @param $arr
     * @param $fieldNames
     */
    public static function copyDataFromArrayToDocument($document, array $arr, array $fieldNames)
    {
        foreach ($fieldNames as $attributeName) {
            $myDoc = $document;
            $myArr = $arr;

            $subfields = explode('.', $attributeName);
            $lastSubfield = array_pop($subfields);
            $setterName = 'set' . ucfirst($lastSubfield);

            foreach ($subfields as $subfield) {
                $getterName = 'get' . ucfirst($subfield);
                $myDoc = $myDoc->$getterName();

                $myArr = @$myArr[$subfield];
            }
            $myDoc->$setterName(@$myArr[$lastSubfield]);
        }
    }



    /**
     * used by ebaygen5
     *
     * @param array $src
     * @param AbstractDocument $dest the document with getters and setters
     * @param array $fieldNames
     */
    public static function copyFromArray( array $src, $dest, array $fieldNames)
    {
        foreach( $fieldNames as $fieldName) {
            $setterName = "set".ucfirst($fieldName);
            $dest->$setterName( @$src[$fieldName]);
        }
    }



}
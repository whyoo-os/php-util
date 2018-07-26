<?php


namespace WhyooOs\Util;



class UtilDocument
{


    /**
     * used by ebaygen5, schlegel
     *
     * can also handle field names like inventoryItem.currentStockLevel for embedded stuff
     *
     * @param $srcArray
     * @param \stdClass (AbstractDocument) $destDocument the document with getters and setters
     * @param $fieldNames
     */
    public static function copyDataFromArrayToDocument(array $srcArray, $destDocument, array $fieldNames)
    {
        foreach ($fieldNames as $attributeName) {
            $myDoc = $destDocument;
            $myArr = $srcArray;

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
     * 07/2018
     * checks if setter exists
     *
     * TODO? subfields?
     *
     * @param $formatterArr
     * @param $object
     * @param string[] $blacklist
     */
    public static function copyDataFromArrayToDocumentWithBlacklist($formatterArr, $object, array $blacklist)
    {
        foreach ($formatterArr as $key => $value) {
            if (in_array($key, $blacklist)) {
                continue;
            }
            $setterName = 'set' . ucfirst($key);
            if( method_exists($object, $setterName)) {
                $object->$setterName($value);
            }
        }
    }



//    /**
//     * not used by ebaygen5
//     *
//     * @param array $src
//     * @param AbstractDocument $dest the document with getters and setters
//     * @param array $fieldNames
//     */
//    public static function copyFromArray( array $src, $dest, array $fieldNames)
//    {
//        foreach( $fieldNames as $fieldName) {
//            $setterName = "set".ucfirst($fieldName);
//            $dest->$setterName( @$src[$fieldName]);
//        }
//    }



}
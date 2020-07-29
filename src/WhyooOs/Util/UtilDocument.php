<?php


namespace WhyooOs\Util;


/**
 * 07/2017
 */
class UtilDocument
{


    /**
     * used by mcxlister, schlegel
     *
     * can also handle field names like inventoryItem.currentStockLevel for embedded stuff
     *
     * @param $srcArray
     * @param \stdClass (AbstractDocument) $destDocument the document with getters and setters
     * @param $whiteList
     */
    public static function copyDataFromArrayToDocument(array $srcArray, $destDocument, array $whiteList)
    {
        foreach ($whiteList as $attributeName) {
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
     * copies properties from one document to another
     * used by mcx-lister ... useful for making a clone of a document
     * 03/2020
     *
     * @param \stdClass (AbstractDocument) $srcDocument
     * @param \stdClass (AbstractDocument) $destDocument the document with getters and setters
     * @param string[] $whiteList
     */
    public static function copyDataFromDocumentToDocument($srcDocument, $destDocument, array $whiteList)
    {
        foreach ($whiteList as $propertyName) {
            $getterName = 'get' . ucfirst($propertyName);
            $setterName = 'set' . ucfirst($propertyName);
            $destDocument->$setterName($srcDocument->$getterName());
        }
    }

    /**
     * copies properties from one document to an assoc array
     *
     * 07/2020 unused/untested
     *
     * @param \stdClass (AbstractDocument) $srcDocument
     * @param array $destArray an Assoc Array
     * @param string[] $whiteList
     * @return array the $destArray
     */
    public static function copyDataFromDocumentToArray($srcDocument, array $destArray, array $whiteList)
    {
        foreach ($whiteList as $propertyName) {
            $getterName = 'get' . ucfirst($propertyName);
            $destArray[$propertyName] = $srcDocument->$getterName();
        }

        return $destArray;
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
//     * not used by mcxlister
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



    /**
     * 04/2019 used by mcxlister for sorting products by reorderStatus
     * 12/2019 moved to UtilDocument from UtilArray
     *
     * @param array $array
     * @param string $key
     * @param array $customOrder
     * @return array
     */
    public static function sortDocumentsByCustomOrder(array $array, string $key, array $customOrder)
    {
        $getter = 'get' . ucfirst($key);
        usort($array, function ($a, $b) use ($customOrder, $getter) {
            $pos_b = array_search($b->$getter(), $customOrder);
            $pos_a = array_search($a->$getter(), $customOrder);
            return $pos_a - $pos_b;
        });

        return $array;
    }




}
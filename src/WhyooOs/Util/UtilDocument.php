<?php


namespace WhyooOs\Util;


/**
 * 07/2017
 */
class UtilDocument
{
    /**
     * used by cloudlister, schlegel, mb
     *
     * can also handle field names like inventoryItem.currentStockLevel for embedded stuff
     *
     * 09/2020 renamed copyDataFromArrayToDocument --> copyDataFromArrayToDocumentWithWhitelist
     * 11/2021 parameter $bSkipNonExisting added (true for PATCH, false for PUT/POST)
     *
     * @param array $srcArray
     * @param \stdClass (AbstractDocument) $destDocument the document with getters and setters
     * @param string[] $whiteList list of paths, dots are supported
     * @param bool $bSkipNonExisting skip if a field from whitelist does not exist in srcArray if true, set it to NULL if false
     */
    public static function copyDataFromArrayToDocumentWithWhitelist(array $srcArray, $destDocument, array $whiteList, bool $bSkipNonExisting = false)
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

                if (array_key_exists($subfield, $myArr)) {
                    $myArr = $myArr[$subfield];
                } else {
                    // not found in srcArray ..
                    $myArr = [];
                }
            }

            if (array_key_exists($lastSubfield, $myArr)) {
                $myDoc->$setterName($myArr[$lastSubfield]);
            } else {
                if (!$bSkipNonExisting) {
                    // set explicitly to null
                    $myDoc->$setterName(null);
                }
            }
        }
    }


    /**
     * 07/2018
     *
     * @param $srcArray
     * @param $destObject
     * @param string[] $blacklist
     */
    public static function copyDataFromArrayToDocumentWithBlacklist($srcArray, $destObject, array $blacklist)
    {
        $whiteList = array_diff(array_keys($srcArray), $blacklist);
        self::copyDataFromArrayToDocumentWithWhitelist($srcArray, $destObject, $whiteList);
    }


    /**
     * 09/2020 created
     * used by cloudlister
     *
     * can also handle field names like inventoryItem.currentStockLevel for embedded stuff
     *
     * @param $srcArray
     * @param \stdClass (AbstractDocument) $destDocument the document with getters and setters
     */
    public static function copyDataFromArrayToDocument(array $srcArray, $destDocument)
    {
        self::copyDataFromArrayToDocumentWithWhitelist($srcArray, $destDocument, array_keys($srcArray));
    }


    /**
     * copies properties from one document to another
     * used by mcx-lister ... useful for making a clone of a document
     * 03/2020
     *
     * @param \stdClass (AbstractDocument) $srcDocument
     * @param \stdClass (AbstractDocument) $destDocument the document with getters and setters
     * @param $whiteList
     */
    public static function copyDataFromDocumentToDocument($srcDocument, $destDocument, array $whiteList)
    {
        foreach ($whiteList as $propertyName) {
            $getterName = 'get' . ucfirst($propertyName);
            $setterName = 'set' . ucfirst($propertyName);
            $destDocument->$setterName($srcDocument->$getterName());
        }
    }





//    /**
//     * not used by cloudlister
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
     * 04/2019 used by cloudlister for sorting products by reorderStatus
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


    /**
     * 05/2021 created push4
     *
     * @param $document
     * @param string $path
     * @param bool $bExceptionOnNotFound
     * @return mixed
     * @throws \Exception
     */
    public static function deepGet($document, string $path, bool $bExceptionOnNotFound = true)
    {
        $subfields = explode('.', $path);

        foreach ($subfields as $fieldName) {
            $getterName = 'get' . ucfirst($fieldName);
            if (!method_exists($document, $getterName)) {
                if ($bExceptionOnNotFound) {
                    throw new \Exception("path '$path' does not exist");
                } else {
                    return null;
                }
            }
            $document = $document->$getterName();
        }

        return $document;
    }

    /**
     * 03/2023 created
     *
     * @param $document
     * @param mixed $path
     * @param mixed $val
     * @param bool $bExceptionOnNotFound
     * @return void
     * @throws \Exception
     */
    public static function setDeep($document, string $path, mixed $val, bool $bExceptionOnNotFound = true)
    {
        $subfields = explode('.', $path);
        $subfieldsExceptLast = array_slice($subfields, 0, count($subfields) - 1 );
        $lastSubfield = $subfields[count($subfields) - 1];

        foreach ($subfieldsExceptLast as $fieldName) {
            $getterName = 'get' . ucfirst($fieldName);
            if (!method_exists($document, $getterName)) {
                if ($bExceptionOnNotFound) {
                    throw new \Exception("path '$path' does not exist");
                } else {
                    return null;
                }
            }
            $document = $document->$getterName();
        }

        $setterName = 'set' . ucfirst($lastSubfield);
        $document->$setterName($val);
    }


}

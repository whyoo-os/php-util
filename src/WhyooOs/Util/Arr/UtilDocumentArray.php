<?php

namespace WhyooOs\Util\Arr;

/**
 * 05/2021 created
 */
class UtilDocumentArray
{
    /**
     * 02/2018 unused
     * 08/2020 used by cloudlister
     * 08/2020 used by tldr-to-anki
     * 09/2020 used by language immerser
     * 05/2021 moved from UtilArray to UtilDocumentArray
     *
     * @param array $items
     * @param string $getterName eg "getId"
     * @return array
     */
    public static function arrayColumnByGetter(array $items, string $getterName): array
    {
        return array_map(function ($f) use ($getterName) {
            return $f->$getterName();
        }, $items);
    }


    /**
     * 02/2018 unused
     * 08/2020 used by cloudlister
     * 08/2020 used by tldr-to-anki
     * 09/2020 used by language immerser
     * 05/2021 moved from UtilArray to UtilDocumentArray
     *
     * @param array $items
     * @param string $columnName eg "id" ... the gettername is generated automatically
     * @return array
     */
    public static function arrayColumn(array $items, string $columnName): array
    {
        $getterName = 'get' . ucfirst($columnName);
        return array_map(function ($f) use ($getterName) {
            return $f->$getterName();
        }, $items);
    }

    /**
     * get single property of documents in an array using getters,
     * also by sub-documents like 'userProfile.birthday' are possible
     *
     * example:
     *
     * $posts = {'a' => post1, 'b' => post2, 'c' => post3]
     * getObjectProperty($posts, 'id', false) returns [1,2,3]
     * getObjectProperty($posts, 'id', true) returns { a:1, b:2, c:3 }
     *
     * used in marketer
     *
     * 05/2021 moved from UtilArray::getObjectProperty() to UtilDocumentArray::arrayColumnDeep()
     *
     * @param array $arr
     * @param string $propertyName also sub-documents like 'userProfile.birthday' are possible
     * @param bool $bKeepOriginalKeys the thing with $bKeepOriginalKeys` is if array is associative to keep the old keys not to cretae new numeric array
     * @return array
     */
    public static function arrayColumnDeep(array $arr, $propertyName, $bKeepOriginalKeys = false)
    {
        $subfields = explode('.', $propertyName);

        $getterNames = [];
        foreach ($subfields as $subfield) {
            $getterNames[] = 'get' . ucfirst($subfield);
        }


        // $methodName = "get" . ucfirst($propertyName);

        if ($bKeepOriginalKeys) {
            // keep original keys
            array_walk($arr, function (&$item, $key) use ($getterNames) {
                foreach ($getterNames as $getterName) {
                    $item = $item->$getterName();
                }
            });
            $newArray = $arr;
        } else {
            // new keys (create ordinary numeric array)
            $newArray = array_map(function ($item) use ($getterNames) {
                foreach ($getterNames as $getterName) {
                    $item = $item->$getterName();
                }
                return $item;
            }, $arr);
        }

        return $newArray;
    }

}

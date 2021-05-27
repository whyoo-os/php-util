<?php

namespace WhyooOs\Util\Arr;

/**
 * 03/2021 created
 */
class UtilArraySort
{

    /**
     * sorts an array of assoc arrays (= a table) by a column $keyName
     *
     * 03/2021 moved from UtilArray::sortArrayOfArrays to UtilArraySort::sortDicts()
     *
     * @param array &$dicts
     * @param string $keyName
     * @param int $sortOrder
     * @param int $sortFlags
     * @return array
     */
    public static function sortDicts(array &$dicts, string $keyName, int $sortOrder = SORT_ASC, int $sortFlags = SORT_REGULAR)
    {
        $sortArray = [];
        foreach ($dicts as $idx => $row) {
            $sortArray[$idx] = $row[$keyName];
        }
        array_multisort($sortArray, $sortOrder, $sortFlags, $dicts);

        return $dicts;
    }


    /**
     * sorts an array of objects
     * used by marketer (for sorting list of participants by conversationRole)
     * TODO: maybe there is faster version with a callback
     * TODO 05/2021 maybe move to UtilDocumentArray::sortDeep() ?
     *
     * 03/2018
     * 08/2018 also sorts by sub-documents like 'userProfile.birthday'
     * 03/2021 moved from UtilArray::sortArrayOfObjects to UtilArraySort::sortDocuments()
     *
     * @param array &$documents
     * @param string $path eg "conversationRole", "userProfile.birthday"
     * @param int $sortOrder
     * @param int $sortFlags
     * @return array
     */
    public static function sortDocuments(array &$documents, string $path, int $sortOrder = SORT_ASC, int $sortFlags = SORT_REGULAR)
    {
        $sortArray = UtilDocumentArray::arrayColumnDeep($documents, $path);
        if (array_multisort($sortArray, $sortOrder, $sortFlags, $documents) === FALSE) {
            throw new \Exception("multisort failed");
        }

        return $documents;
    }


    /**
     * 03/2021 created
     *
     * @param array $documents
     * @param array $sortBy, eg: ['width' => SORT_DESC, 'depth' => SORT_DESC]
     * @return array|mixed
     * @throws \Exception
     */
    public static function multisortDocuments(array &$documents, array $sortBy)
    {
        $funArgs = [];
        foreach ($sortBy as $columnName => $sortOrder) {
            $funArgs[] = UtilDocumentArray::arrayColumnDeep($documents, $columnName);
            $funArgs[] = $sortOrder;
        }
        $funArgs[] = &$documents;

        if (call_user_func_array('array_multisort', $funArgs) === FALSE) {
            throw new \Exception("multisort failed");
        }

        return $documents;
    }




}

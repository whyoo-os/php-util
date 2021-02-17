<?php

namespace WhyooOs\Util;


/**
 *
 */
class UtilAssert
{

    /**
     * @param $bAssertion
     * @param string $errorMessage
     * @throws \AssertionError
     */
    public static function assertTrue($bAssertion, $errorMessage = "")
    {
        if ($bAssertion != true) {
            throw new \AssertionError(__METHOD__ . " failed. " . $errorMessage);
        }
    }

    /**
     * @param $subject
     * @param string $errorMessage
     * @throws \AssertionError
     */
    public static function assertGreaterZero($number, $errorMessage = "")
    {
        if (!($number > 0)) {
            throw new \AssertionError(__METHOD__ . " failed ($number <= 0). " . $errorMessage);
        }
    }

    /**
     * @param $subject
     * @param string $errorMessage
     * @throws \AssertionError
     */
    public static function assertZero($number, $errorMessage = "")
    {
        if (($number != 0)) {
            throw new \AssertionError(__METHOD__ . " failed ($number != 0). " . $errorMessage);
        }
    }

    /**
     * @param $subject
     * @param string $errorMessage
     * @throws \AssertionError
     */
    public static function assertNull($subject, $errorMessage = "")
    {
        if (is_null($subject)) {
            throw new \AssertionError(__METHOD__ . " failed (got " . (string)$subject . "). " . $errorMessage);
        }
    }


    /**
     * @param $subject
     * @param string $errorMessage
     * @throws \AssertionError
     */
    public static function assertNotNull($subject, $errorMessage = "")
    {
        if (is_null($subject)) {
            throw new \AssertionError(__METHOD__ . " failed. " . $errorMessage);
        }
    }


    /**
     * @param $subject
     * @param string $errorMessage
     * @throws \AssertionError
     */
    public static function assertNotEmpty($subject, $errorMessage = "")
    {
        if (empty($subject)) {
            throw new \AssertionError(__METHOD__ . " failed " . $errorMessage);
        }
    }


    /**
     * 09/2020 created (slides-mailer)
     *
     * @param $subject
     * @param string $errorMessage
     * @throws \AssertionError
     */
    public static function assertEmpty($subject, $errorMessage = "")
    {
        if (!empty($subject)) {
            throw new \AssertionError(__METHOD__ . " failed " . $errorMessage);
        }
    }

    /**
     * @param $v1
     * @param $v2
     * @param string $errorMessage
     * @throws \AssertionError
     */
    public static function assertEqual($v1, $v2, $errorMessage = "")
    {
        if ($v1 != $v2) {
            throw new \AssertionError(__METHOD__ . " failed ($v1 != $v2). " . $errorMessage);
        }
    }

    /**
     * 09/2018
     *
     * @param $v1
     * @param $v2
     * @param string $errorMessage
     * @throws \AssertionError
     */
    public static function assertNotEqual($v1, $v2, $errorMessage = "")
    {
        if ($v1 == $v2) {
            throw new \AssertionError(__METHOD__ . " failed ($v1 == $v2) " . $errorMessage);
        }
    }


    /**
     * 08/2018
     * @param $bAssertion
     * @param string $errorMessage
     * @throws \AssertionError
     */
    public static function assertMaxAbsDelta($v1, $v2, $maxAbsDelta, $errorMessage = "")
    {
        if (abs($v1 - $v2) > $maxAbsDelta) {
            $absDelta = abs($v1 - $v2);
            throw new \AssertionError(__METHOD__ . " failed (abs($v1 - $v2) = $absDelta > $maxAbsDelta) " . $errorMessage);
        }
    }


    /**
     * @param $bAssertion
     * @param string $errorMessage
     * @throws \AssertionError
     */
    public static function assertFalse($bAssertion, $errorMessage = "")
    {
        if ($bAssertion != false) {
            throw new \AssertionError(__METHOD__ . " failed: " . $errorMessage);
        }
    }


    /**
     * @param mixed $needle
     * @param array $haystack
     * @param string $errorMessage
     * @throws \AssertionError
     */
    public static function assertInArray($needle, array $haystack, string $errorMessage = '')
    {
        if (!in_array($needle, $haystack)) {
            $strAvailable = implode(', ', $haystack);
            throw new \AssertionError(__METHOD__ . " failed - '$needle' not in array'. available values: $strAvailable." . $errorMessage);
        }
    }


    /**
     * @param $key
     * @param array $haystack
     * @param string $errorMessage
     * @throws \AssertionError
     */
    public static function assertArrayKeyExists($key, array $haystack, string $errorMessage = '')
    {
        if (!array_key_exists($key, $haystack)) {
            $strAvailable = implode(', ', array_keys($haystack));
            throw new \AssertionError(__METHOD__ . " failed - no '$key' in array keys. available keys: $strAvailable." . "\n" . $errorMessage);
        }
    }

    public static function assertClass($object, string $neededClass, $errorMessage = 'class mismatch')
    {
        $actualClass = is_object($object) ? get_class($object) : gettype($object);
        if ($actualClass != $neededClass) {
            throw new \AssertionError(__METHOD__ . " failed: $actualClass != $neededClass. " . $errorMessage);
        }
    }

    public static function assertIsObject($object, string $errorMessage = '')
    {
        if (!is_object($object)) {
            throw new \AssertionError(__METHOD__ . " failed: " . gettype($object) . ". " . $errorMessage);
        }
    }


    // 01/2018
    public static function assertIsArray($array, string $errorMessage = '')
    {
        if (!is_array($array)) {
            throw new \AssertionError(__METHOD__ . " failed: " . gettype($array) . ". " . $errorMessage);
        }
    }

    public static function assertNotInstanceOf($object, string $forbiddenClass, string $errorMessage = '')
    {
        if ($object instanceof $forbiddenClass) {
            $actualClass = get_class($object);
            throw new \AssertionError(__METHOD__ . " failed: $actualClass is instance of $forbiddenClass. " . $errorMessage);
        }
    }

    /**
     * 08/2018
     */
    public static function assertInstanceOf($object, string $class, string $errorMessage = '')
    {
        if (!($object instanceof $class)) {
            $actualClass = get_class($object);
            throw new \AssertionError(__METHOD__ . " failed: $actualClass is not instance of $class. " . $errorMessage);
        }
    }

    public static function assertFileExists($pathFile, string $errorMessage = '')
    {
        if (!file_exists($pathFile)) {
            throw new \AssertionError(__METHOD__ . " failed for path $pathFile. " . $errorMessage);
        }
    }

    public static function assertIsFile($pathFile, string $errorMessage = '')
    {
        if (!is_file($pathFile)) {
            throw new \AssertionError(__METHOD__ . " failed for path $pathFile. " . $errorMessage);
        }
    }

    /**
     * 12/2020 created
     * used by cloudlister/AppMailer
     *
     * @param $pathsFiles
     * @param string $errorMessage
     */
    public static function assertAreFiles($pathsFiles, string $errorMessage = '')
    {
        foreach($pathsFiles as $pathFile) {
            if (!is_file($pathFile)) {
                throw new \AssertionError(__METHOD__ . " failed for path $pathFile. " . $errorMessage);
            }
        }
    }

    /**
     * 01/2020
     *
     * @param $pathFile
     * @param string $errorMessage
     * @throws \AssertionError
     */
    public static function assertFileDoesNotExists($pathFile, string $errorMessage = '')
    {
        if (file_exists($pathFile)) {
            throw new \AssertionError(__METHOD__ . " failed for path $pathFile. " . $errorMessage);
        }
    }


    /**
     * 01/2020
     *
     * @param string $pathFile
     * @throws \AssertionError
     */
    public static function assertIsWriteable(string $pathFile, string $errorMessage = '')
    {
        if (!is_writeable($pathFile)) {
            throw new \AssertionError(__METHOD__ . " failed for path $pathFile. " . $errorMessage);
        }
    }


    public static function assertIsInt($x, string $errorMessage = '')
    {
        if (!is_int($x)) {
            throw new \AssertionError(__METHOD__ . " failed for path $x. " . $errorMessage);
        }
    }

    public static function assertIsDir($pathDir, string $errorMessage = '')
    {
        if (!is_dir($pathDir)) {
            throw new \AssertionError(__METHOD__ . " failed for path $pathDir. " . $errorMessage);
        }
    }

    /**
     * asserts that array has specific length
     * 08/2017
     * 06/2020 also handles \Traversable
     * WARNING: if $array is a \Traversable it is not guaranteed that the current position of the iterator is retained
     *
     * @param array|\Traversable $array
     * @param int $length
     * @param string $errorMessage
     * @throws \AssertionError
     */
    public static function assertArrayLengthEquals($array, int $length, string $errorMessage = '')
    {
        // ---- array
        if (is_array($array)) {
            if (count($array) != $length) {
                throw new \AssertionError(__METHOD__ . " failed: Array length " . count($array) . " != $length. " . $errorMessage);
            }
        }

        // ---- \Traversable
        if ($array instanceof \Traversable) {
            $ic = iterator_count($array);
            if ($ic != $length) {
                throw new \AssertionError(__METHOD__ . " failed: Traversable length $ic != $length. " . $errorMessage);
            }
        }
    }

    /**
     * asserts that array has maximum length
     * 06/2020 created
     * WARNING: if $array is a \Traversable it is not guaranteed that the current position of the iterator is retained
     *
     * @param array|\Traversable $array
     * @param int $maxLength
     * @param string $errorMessage
     * @throws \AssertionError
     */
    public static function assertArrayLengthLessOrEquals($array, int $maxLength, string $errorMessage = '')
    {
        // ---- array
        if (is_array($array)) {
            if (count($array) > $maxLength) {
                throw new \AssertionError(__METHOD__ . " failed: Array length " . count($array) . " > $maxLength. " . $errorMessage);
            }
        }

        // ---- \Traversable
        if ($array instanceof \Traversable) {
            $ic = iterator_count($array);
            if ($ic > $maxLength) {
                throw new \AssertionError(__METHOD__ . " failed: Traversable length $ic > $maxLength. " . $errorMessage);
            }
        }
    }


    /**
     * 11/2017
     *
     * @param string $haystack
     * @param string $needle
     * @param string $errorMessage
     * @throws \AssertionError
     */
    public static function assertStringIncludes(string $haystack, string $needle, string $errorMessage = '')
    {
        if (strpos($haystack, $needle) === false) {
            throw new \AssertionError(__METHOD__ . " failed: string `$needle` not included in string `$haystack`. " . $errorMessage);
        }
    }


    /**
     * 08/2018
     *
     * @param array $array
     * @param string $errorMessage
     * @throws \AssertionError
     */
    public static function assertArrayHasNoDuplicates(array $array, string $errorMessage = '')
    {
        if (UtilArray::hasDuplicates($array)) {
            $duplicates = json_encode(UtilArray::getDuplicates($array));
            throw new \AssertionError(__METHOD__ . " failed: array has duplicate/s: $duplicates " . $errorMessage);
        }
    }


    /**
     * @param string $haystack
     * @param string $needle
     * @throws \AssertionError
     */
    public static function assertStringStartsWith(string $haystack, string $needle, string $errorMessage = '')
    {
        if (!UtilString::startsWith($haystack, $needle)) {
            throw new \AssertionError(__METHOD__ . " failed: string `$haystack` does not start with `$needle`. " . $errorMessage);
        }
    }

    /**
     * 09/2020 created
     * used by language immerser
     *
     * @param array $arr1
     * @param array $arr2
     * @throws \AssertionError
     */
    public static function assertArraysSameLength(array $arr1, array $arr2, string $errorMessage = '')
    {
        if (count($arr1) !== count($arr2)) {
            throw new \AssertionError(__METHOD__ . " failed: Array length " . count($arr1) . " != " . count($arr2) . " " . $errorMessage);
        }
    }

    /**
     * 09/2020 created
     * used by cloudlister
     *
     * @param $var
     * @param string $errorMessage
     */
    public static function assertIsNonEmptyString($var, string $errorMessage = '')
    {
        if (!is_string($var) || empty($var)) {
            throw new \AssertionError(__METHOD__ . " failed for value " . $var . " " . $errorMessage);
        }
    }

}


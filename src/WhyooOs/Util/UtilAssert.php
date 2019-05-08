<?php

namespace WhyooOs\Util;

/**
 * 04/2018
 * AssertException
 */
class AssertException extends \Exception
{
}


/**
 *
 */
class UtilAssert
{

    /**
     * @param $bAssertion
     * @param string $errorMessage
     * @throws AssertException
     */
    public static function assertTrue($bAssertion, $errorMessage = "")
    {
        if ($bAssertion != true) {
            throw new AssertException(__METHOD__ . " failed. " . $errorMessage);
        }
    }

    /**
     * @param $subject
     * @param string $errorMessage
     * @throws AssertException
     */
    public static function assertGreaterZero($number, $errorMessage = "")
    {
        if (!($number > 0)) {
            throw new AssertException(__METHOD__ . " failed ($number <= 0). " . $errorMessage);
        }
    }

    /**
     * @param $subject
     * @param string $errorMessage
     * @throws AssertException
     */
    public static function assertZero($number, $errorMessage = "")
    {
        if (($number != 0)) {
            throw new AssertException(__METHOD__ . " failed ($number != 0). " . $errorMessage);
        }
    }

    /**
     * @param $subject
     * @param string $errorMessage
     * @throws AssertException
     */
    public static function assertNull($subject, $errorMessage = "")
    {
        if (is_null($subject)) {
            throw new AssertException(__METHOD__ . " failed (got " . (string)$subject . "). " . $errorMessage);
        }
    }


    /**
     * @param $subject
     * @param string $errorMessage
     * @throws AssertException
     */
    public static function assertNotNull($subject, $errorMessage = "")
    {
        if (is_null($subject)) {
            throw new AssertException(__METHOD__ . " failed. " . $errorMessage);
        }
    }


    /**
     * @param $subject
     * @param string $errorMessage
     * @throws AssertException
     */
    public static function assertNotEmpty($subject, $errorMessage = "")
    {
        if (empty($subject)) {
            throw new AssertException(__METHOD__ . " failed " . $errorMessage);
        }
    }

    /**
     * @param $v1
     * @param $v2
     * @param string $errorMessage
     * @throws AssertException
     */
    public static function assertEqual($v1, $v2, $errorMessage = "")
    {
        if ($v1 != $v2) {
            throw new AssertException(__METHOD__ . " failed ($v1 != $v2) " . '@'.$errorMessage.'@');
        }
    }

    /**
     * 09/2018
     *
     * @param $v1
     * @param $v2
     * @param string $errorMessage
     * @throws AssertException
     */
    public static function assertNotEqual($v1, $v2, $errorMessage = "")
    {
        if ($v1 == $v2) {
            throw new AssertException(__METHOD__ . " failed ($v1 == $v2) " . $errorMessage);
        }
    }


    /**
     * 08/2018
     * @param $bAssertion
     * @param string $errorMessage
     * @throws AssertException
     */
    public static function assertMaxAbsDelta($v1, $v2, $maxAbsDelta, $errorMessage = "")
    {
        if (abs($v1 - $v2) > $maxAbsDelta) {
            $absDelta = abs($v1 - $v2);
            throw new AssertException(__METHOD__ . " failed (abs($v1 - $v2) = $absDelta > $maxAbsDelta) " . $errorMessage);
        }
    }


    /**
     * @param $bAssertion
     * @param string $errorMessage
     * @throws AssertException
     */
    public static function assertFalse($bAssertion, $errorMessage = "")
    {
        if ($bAssertion != false) {
            throw new AssertException(__METHOD__ . " failed: " . $errorMessage);
        }
    }


    /**
     * @param mixed $needle
     * @param array $haystack
     * @param string $errorMessage
     * @throws AssertException
     */
    public static function assertInArray($needle, array $haystack, $errorMessage = '')
    {
        if (!in_array($needle, $haystack)) {
            $strAvailable = implode(', ', $haystack);
            throw new AssertException(__METHOD__ . " failed - '$needle' not in array'. available values: $strAvailable." . $errorMessage);
        }
    }


    /**
     * @param $key
     * @param array $haystack
     * @param string $errorMessage
     * @throws AssertException
     */
    public static function assertArrayKeyExists($key, array $haystack, $errorMessage = '')
    {
        if (!array_key_exists($key, $haystack)) {
            $strAvailable = implode(', ', array_keys($haystack));
            throw new AssertException(__METHOD__ . " failed - no '$key' in array keys. available keys: $strAvailable." . "\n" . $errorMessage);
        }
    }

    public static function assertClass($object, string $neededClass, $errorMessage = 'class mismatch')
    {
        $actualClass = is_object($object) ? get_class($object) : gettype($object);
        if ($actualClass != $neededClass) {
            throw new AssertException(__METHOD__ . " failed: $actualClass != $neededClass. " . $errorMessage);
        }
    }

    public static function assertIsObject($object, $errorMessage = '')
    {
        if (!is_object($object)) {
            throw new AssertException(__METHOD__ . " failed: " . gettype($object) . ". " . $errorMessage);
        }
    }


    // 01/2018
    public static function assertIsArray($array, $errorMessage = '')
    {
        if (!is_array($array)) {
            throw new AssertException(__METHOD__ . " failed: " . gettype($array) . ". " . $errorMessage);
        }
    }

    public static function assertNotInstanceOf($object, string $forbiddenClass, $errorMessage = '')
    {
        if ($object instanceof $forbiddenClass) {
            $actualClass = get_class($object);
            throw new AssertException(__METHOD__ . " failed: $actualClass is instance of $forbiddenClass. " . $errorMessage);
        }
    }

    /**
     * 08/2018
     */
    public static function assertInstanceOf($object, string $class, $errorMessage = '')
    {
        if (!($object instanceof $class)) {
            $actualClass = get_class($object);
            throw new AssertException(__METHOD__ . " failed: $actualClass is not instance of $class. " . $errorMessage);
        }
    }

    public static function assertFileExists($pathFile, $errorMessage = '')
    {
        if (!file_exists($pathFile)) {
            throw new AssertException(__METHOD__ . " failed for path $pathFile. " . $errorMessage);
        }
    }

    public static function assertIsFile($pathFile, $errorMessage = '')
    {
        if (!is_file($pathFile)) {
            throw new AssertException(__METHOD__ . " failed for path $pathFile. " . $errorMessage);
        }
    }

    public static function assertIsInt($x, $errorMessage = '')
    {
        if (!is_int($x)) {
            throw new AssertException(__METHOD__ . " failed for path $x. " . $errorMessage);
        }
    }

    public static function assertIsDir($pathDir, $errorMessage = '')
    {
        if (!is_dir($pathDir)) {
            throw new AssertException(__METHOD__ . " failed for path $pathDir. " . $errorMessage);
        }
    }

    /**
     * asserts that array has specific length
     * 08/2017
     *
     * @param array $array
     * @param int $length
     * @param string $errorMessage
     * @throws AssertException
     */
    public static function assertArrayLengthEquals(array $array, int $length, $errorMessage = '')
    {
        if (count($array) != $length) {
            throw new AssertException(__METHOD__ . " failed: array length " . count($array) . " != $length. " . $errorMessage);
        }
    }


    /**
     * 11/2017
     *
     * @param string $haystack
     * @param string $needle
     * @param string $errorMessage
     * @throws AssertException
     */
    public static function assertStringIncludes(string $haystack, string $needle, $errorMessage = '')
    {
        if (strpos($haystack, $needle) === false) {
            throw new AssertException(__METHOD__ . " failed: string `$needle` not included in string `$haystack`. " . $errorMessage);
        }
    }


    /**
     * 08/2018
     *
     * @param array $array
     * @param string $errorMessage
     * @throws AssertException
     */
    public static function assertArrayHasNoDuplicates(array $array, $errorMessage = '')
    {
        if (UtilArray::hasDuplicates($array)) {
            $duplicates = json_encode(UtilArray::getDuplicates($array));
            throw new AssertException(__METHOD__ . " failed: array has duplicate/s: $duplicates " . $errorMessage);
        }
    }


}


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
     * @throws \Exception
     */
    public static function assertTrue($bAssertion, $errorMessage = "")
    {
        if ($bAssertion != true) {
            throw new \Exception("Assertion failed: " . $errorMessage);
        }
    }

    /**
     * @param $subject
     * @param string $errorMessage
     * @throws \Exception
     */
    public static function assertNotNull($subject, $errorMessage = "")
    {
        if (is_null($subject)) {
            throw new \Exception("Assertion 'Not Null' failed: " . $errorMessage);
        }
    }


    /**
     * @param $subject
     * @param string $errorMessage
     * @throws \Exception
     */
    public static function assertNotEmpty($subject, $errorMessage = "")
    {
        if (empty($subject)) {
            throw new \Exception("Assertion 'Not Empty' failed: " . $errorMessage);
        }
    }

    /**
     * @param $bAssertion
     * @param string $errorMessage
     * @throws \Exception
     */
    public static function assertEqual($v1, $v2, $errorMessage = "")
    {
        if ($v1 != $v2) {
            throw new \Exception("Assertion failed ($v1 != $v2):" . $errorMessage);
        }
    }

    /**
     * @param $bAssertion
     * @param string $errorMessage
     * @throws \Exception
     */
    public static function assertFalse($bAssertion, $errorMessage = "")
    {
        if ($bAssertion != false) {
            throw new \Exception("Assertion failed: " . $errorMessage);
        }
    }

    /**
     * @param mixed $needle
     * @param array $haystack
     * @param string $errorMessage
     * @throws \Exception
     */
    public static function assertInArray($needle, array $haystack, $errorMessage='')
    {
        if (!in_array($needle, $haystack)) {
            throw new \Exception("InArray Assertion failed - '$needle' not in array'" . $errorMessage);
        }
    }

    public static function assertClass($object, string $neededClass, $errorMessage = 'class mismatch')
    {
        $actualClass = get_class($object);
        if ($actualClass != $neededClass) {
            throw new \Exception("is class assertion failed $actualClass != $neededClass. " . $errorMessage);
        }
    }

    public static function assertNotInstanceOf($object, string $forbiddenClass, $errorMessage='')
    {
        if ($object instanceof $forbiddenClass) {
            $actualClass = get_class($object);
            throw new \Exception("NotInstanceOf assertion failed $actualClass is instance of $forbiddenClass. " . $errorMessage);
        }
    }

    public static function assertFileExists($pathFile, $errorMessage = '')
    {
        if (!file_exists($pathFile)) {
            throw new \Exception("file-exists assertion failed for path $pathFile. " . $errorMessage);
        }
    }

    public static function assertIsFile($pathFile, $errorMessage = '')
    {
        if (!is_file($pathFile)) {
            throw new \Exception("is-file assertion failed for path $pathFile. " . $errorMessage);
        }
    }

    public static function assertIsDir($pathDir, $errorMessage = '')
    {
        if (!is_dir($pathDir)) {
            throw new \Exception("is-dir assertion failed for path $pathDir. " . $errorMessage);
        }
    }

    /**
     * asserts that array has specific length
     * 08/2017
     *
     * @param $array
     * @param $length
     * @throws \Exception
     */
    public static function assertArrayLength(array $array, int $length, $errorMessage='')
    {
        if (count($array) != $length) {
            throw new \Exception("Assertion failed: array length " . count($array) . " != $length. " . $errorMessage);
        }
    }

}


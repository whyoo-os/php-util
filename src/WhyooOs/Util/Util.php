<?php


namespace WhyooOs\Util;


use Colors\Color;

class Util
{

    /**
     * TODO: move this somewhere else..
     *
     * @return bool
     */
    public static function isLive(): bool
    {
        return preg_match('#^/home/marc/devel/#', __DIR__) == 0;
    }


    /**
     * TODO: move this somewhere else..
     *
     * @return bool
     */
    public static function isDevel(): bool
    {
        return !self::isLive();
    }


    /**
     * TODO: move to UtilMongo
     * 05/2020 \MongoDB\BSON\ObjectId added
     *
     * @return \MongoDB\BSON\ObjectId|\MongoId
     */
    public static function createMongoId()
    {
        if (class_exists(\MongoDB\BSON\ObjectId::class)) {
            return new \MongoDB\BSON\ObjectId();
        }
        return new \MongoId(); // deprecated
        // LATER: return new \MongoDB\BSON\ObjectId();
    }


    /**
     * TODO: move to UtilMongo
     *
     * @param $str
     * @return int
     */
    public static function isMongoId($str)
    {
        return preg_match('/^[a-f\d]{24}$/i', $str);
    }


    /**
     * TODO: move to UtilMongo
     * 05/2020 \MongoDB\BSON\ObjectId added
     *
     * @param $str
     * @return \MongoDB\BSON\ObjectId|\MongoId
     */
    public static function toMongoId($str)
    {
        if (class_exists(\MongoDB\BSON\ObjectId::class)) { // 05/2020
            return new \MongoDB\BSON\ObjectId($str);
        }
        if (self::isMongoId($str)) { // deprecated
            return new \MongoId($str);
        }
    }

    /**
     * 09/2017 .. used by UtilCurl to put cookies.txt and curl_cache in directory of calling script
     *
     * @return string absolute path of calling script
     */
    public static function getCallingScript()
    {
        $stack = debug_backtrace();
        $firstFrame = $stack[count($stack) - 1];

        return $firstFrame['file'];
    }


    /**
     * @param \Doctrine\Common\Collections\ArrayCollection|\Doctrine\ODM\MongoDB\Cursor|\MongoCursor|array $arr
     * @return array
     */
    public static function toArray($arr, $useKeys = true)
    {
        if (is_array($arr)) {
            return $arr;
        }
        return iterator_to_array($arr, $useKeys);
        #return $arr->toArray();
    }


    /**
     * TODO: move to UtilCommandLine
     *
     * @return string
     */
    public static function waitKeypress()
    {
        echo "\npress enter\n";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        fclose($handle);
        return trim($line);
    }


    /**
     * TODO: move to UtilFormatter
     *
     * @param $size
     * @param string|null $sizeUnit
     * @param int $numDecimals
     * @return string
     * @throws AssertException
     */
    public static function humanReadableSize($size, string $sizeUnit = null, int $numDecimals = 2)
    {
        $sizeUnits = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

        if ($sizeUnit !== null) {
            UtilAssert::assertInArray($sizeUnit, $sizeUnits);
            $unitIdx = array_search($sizeUnit, $sizeUnits);
        } else {
            $unitIdx = floor(log($size, 1024));
        }

        $divider = pow(1024, $unitIdx);

        return $size ? round($size / $divider, $numDecimals) . ' ' . $sizeUnits[$unitIdx] : '0 Bytes';
    }


    /**
     * TODO: belongs to UtilDocument
     *
     * @param $row
     * @param string $propertyName eg 'calculation.revenueGross'
     * @return mixed
     */
    public static function getPropertyDeep($row, $propertyName)
    {
        $parts = explode('.', $propertyName);
        foreach ($parts as $part) {
            //dump(@get_class($row));
            if (is_null($row)) {
                //dump($parts, $rowClone);
                return null;
            }
            if (is_array($row)) {
                $row = $row[$part];
            } else {
                $getterName = "get" . ucfirst($part);
                $row = $row->$getterName();
            }
        }
        //dump($row);
        return $row;
    }


    /**
     * returns the classname without namespace of obj
     *
     * @param $obj
     * @param int $numBack
     * @return string class name without namespace
     */
    public static function getClassNameShort($obj, int $numBack = 1): ?string
    {
        if ($obj == null) {
            return null;
        }

        return self::removeNamespace(get_class($obj), $numBack);
    }


    /**
     * 08/2020 parameters $delimiter added
     *
     * @param string $classname
     * @param int $numBack
     * @param string $delimiter
     * @return string
     */
    public static function removeNamespace(string $classname, int $numBack = 1, string $delimiter = '\\'): string
    {
        $exploded = explode($delimiter, $classname);
        if (count($exploded) < $numBack) {
            return $classname;
        }
        return implode($delimiter, array_slice($exploded, count($exploded) - $numBack, $numBack));
    }


    /**
     * used for calculation of PricePerPiece (cloudlister)
     * TODO: belongs to UtilNumber
     *
     * @param $number
     * @param int $precision
     * @return float|int
     */
    public static function roundUp($number, $precision = 2)
    {
        $fig = pow(10, $precision);
        return ceil($number * $fig) / $fig;
    }


    /**
     * TODO: belongs to UtilNumber
     *
     * @param $number
     * @param int $precision
     * @return float|int
     */
    public static function roundDown($number, $precision = 2)
    {
        $fig = pow(10, $precision);
        return floor($number * $fig) / $fig;
    }


    // ------------------------------------------------------------------------
    // ------------------------------------------------------------------------
    // ------------------------------------------------------------------------
    // ------------------------------------------------------------------------
    // ------------------------------------------------------------------------
    // ------------------------------------------------------------------------


    /**
     * uses symfony's cache (see config.yml: framework.cache)
     *
     * @param string $key
     * @param mixed $object
     * @param $strExpire
     */
    public static function saveToCache($key, $object, $strExpire = '1 day')
    {
        $cache = UtilSymfony::getContainer()->get('cache.app'); // configures in config.yml
        $cacheItem = $cache->getItem($key);
        $cacheItem->expiresAfter(\DateInterval::createFromDateString($strExpire));
        $cache->save($cacheItem->set($object));
    }


    /**
     * uses symfony's cache (see config.yml: framework.cache)
     *
     * @param string $key
     * @param mixed $object
     * @return mixed|null
     */
    public static function fetchFromCache($key, $default = null)
    {
        $cache = UtilSymfony::getContainer()->get('cache.app'); // configures in config.yml
        $cacheItem = $cache->getItem($key);
        if (!$cacheItem->isHit()) {
            return $default;
        }

        return $cacheItem->get();
    }


    /**
     * 01/2018
     * @return bool
     */
    public static function isCLi()
    {
        return php_sapi_name() == "cli";
    }


    /**
     * @return string \n or <br>
     */
    public static function getNewline()
    {
        if (self::isCli()) {
            // In cli-mode
            return "\n";
        } else {
            return '<br>';
        }
    }


    /**
     * 07/2018
     * used by marketer, next-steps-app
     * needs https://github.com/kevinlebrun/colors.php
     *
     *       composer require kevinlebrun/colors.php
     *
     * @param string $text
     * @param string|null $fg
     * @param string|null $bg
     * @param bool $bold
     */
    public static function printColored(string $text, string $fg = null, string $bg = null, bool $bold = false)
    {
        $c = new Color();
        $c($text);
        if ($fg) {
            $c->fg($fg);
        }
        if ($bg) {
            $c->bg($bg);
        }
        if ($bold) {
            $c->bold();
        }

        echo $c . "\n";
    }


}

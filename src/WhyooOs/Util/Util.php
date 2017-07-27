<?php


namespace WhyooOs\Util;



class Util
{


    static $timeProfilers = [];

    /**
     * can also handle field names like inventoryItem.currentStockLevel for embedded stuff
     *
     * @param $document
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
     * @return bool
     */
    public static function isLive()
    {
        return preg_match('#^/home/marc/devel/#', __DIR__) == 0;
    }


    public static function createMongoId()
    {
        return new \MongoId(); // deprecated
        // LATER: return new \MongoDB\BSON\ObjectId();
    }


    /**
     * @param $str
     * @return int
     */
    public static function isMongoId($str)
    {
        return preg_match('/^[a-f\d]{24}$/i', $str);
    }

    public static function toMongoId($str)
    {
        if (self::isMongoId($str)) {
            return new \MongoId($str);
            // LATER: fix .. it is deprecated
        }
    }


    /**
     * @param  \Doctrine\Common\Collections\ArrayCollection|\Doctrine\ODM\MongoDB\Cursor|\MongoCursor|array $arr
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



    public static function waitKeypress()
    {
        echo "\npress enter\n";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        fclose($handle);
        return trim($line);
    }



    public static function humanReadableSize($size)
    {
        $filesizename = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
        return $size ? round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . $filesizename[$i] : '0 Bytes';
    }

    /**
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
     * removes namespace from FQCN of obj
     * @param $obj
     * @return string class name without namespace
     */
    public static function getClassNameShort($obj, $numBack = 1)
    {
        if( $obj == null) {
            return null;
        }
        return self::removeNamespace(get_class($obj), $numBack);
    }


    /**
     * @param string $class
     * @param int $numBack
     * @return string
     */
    public static function removeNamespace(string $class, int $numBack = 1) : string
    {
        $tmp = explode('\\', $class);

        return implode(".", array_slice($tmp, count($tmp) - $numBack, $numBack));
    }




    /**
     * dump + die
     */
    public static function dd()
    {
        self::d(func_get_args());
        die();
    }

    /**
     * dump
     */
    public static function d()
    {
        $ddSource = debug_backtrace()[0];
        echo("{$ddSource['file']}:{$ddSource['line']}<br>\n");
        foreach (func_get_args() as $arg) {
            dump($arg);
        }
    }




    /**
     * used for calculation of PricePerPiece
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


    public static function roundDown($number, $precision = 2)
    {
        $fig = pow(10, $precision);
        return floor($number * $fig) / $fig;
    }


    public static function simpleLogError($string)
    {
        file_put_contents('/tmp/mcx-simple-log-error.txt', date('Y-m-d H:i') . "\t" . $string . "\n", FILE_APPEND);
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
     * jTraceEx() - provide a Java style exception trace
     * @param $exception
     * @param $seen      - array passed to recursive calls to accumulate trace lines already seen
     *                     leave as NULL when calling this function
     * @return string  nicely formatted exception
     */
    public static function jTraceEx(\Exception $e, $seen=null) {
        $starter = $seen ? 'Caused by: ' : '';
        $result = array();
        if (!$seen) $seen = array();
        $trace  = $e->getTrace();
        $prev   = $e->getPrevious();
        $result[] = sprintf('%s%s: %s', $starter, get_class($e), $e->getMessage());
        $file = $e->getFile();
        $line = $e->getLine();
        while (true) {
            $current = "$file:$line";
            if (is_array($seen) && in_array($current, $seen)) {
                $result[] = sprintf(' ... %d more', count($trace)+1);
                break;
            }
            $result[] = sprintf(' at %s%s%s(%s%s%s)',
                count($trace) && array_key_exists('class', $trace[0]) ? str_replace('\\', '.', $trace[0]['class']) : '',
                count($trace) && array_key_exists('class', $trace[0]) && array_key_exists('function', $trace[0]) ? '.' : '',
                count($trace) && array_key_exists('function', $trace[0]) ? str_replace('\\', '.', $trace[0]['function']) : '(main)',
                $line === null ? $file : basename($file),
                $line === null ? '' : ':',
                $line === null ? '' : $line);
            if (is_array($seen))
                $seen[] = "$file:$line";
            if (!count($trace))
                break;
            $file = array_key_exists('file', $trace[0]) ? $trace[0]['file'] : 'Unknown Source';
            $line = array_key_exists('file', $trace[0]) && array_key_exists('line', $trace[0]) && $trace[0]['line'] ? $trace[0]['line'] : null;
            array_shift($trace);
        }

        $result = join("\n", $result);
        if ($prev) {
            $result .= "\n" . self::jTraceEx($prev, $seen);
        }

        return $result;
    }


    /**
     * used by FixturesUtil
     *
     * @param $pathCsv
     * @return array
     */
    public static function parseCsvFileToObjects($pathCsv)
    {
        $fileHandle = fopen($pathCsv, 'r');
        $arr = [];
        while (($row = fgetcsv($fileHandle)) !== FALSE) {
            $arr[] = $row;
        }
        fclose($fileHandle);

        $headers = array_shift($arr); // remove header row
        $aObjects = [];
        foreach ($arr as $row) {
            if (count($row) == count($headers)) { // valid row
                $aObjects[] = (object)array_combine($headers, $row);
            }
        }

        return $aObjects;
    }



    public static function startTimeProfiling($id='default')
    {
        self::$timeProfilers[$id] = microtime(true);
    }

    /**
     * @param string $id
     * @return float seconds
     */
    public static function stopTimeProfiling($id='default')
    {
        $length = microtime(true) - self::$timeProfilers[$id];
        self::$timeProfilers[$id] = null;

        return $length;
    }






}
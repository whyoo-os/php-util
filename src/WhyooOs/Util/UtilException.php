<?php

namespace WhyooOs\Util;


/**
 * Class UtilException
 * @package WhyooOs\Util
 */
class UtilException
{


    /**
     * jTraceEx() - provide a Java style exception trace
     * @param $exception
     * @param $seen - array passed to recursive calls to accumulate trace lines already seen
     *                     leave as NULL when calling this function
     * @return string  nicely formatted exception
     */
    public static function jTraceEx(\Exception $e, $seen = null)
    {
        $starter = $seen ? 'Caused by: ' : '';
        $result = array();
        if (!$seen) $seen = array();
        $trace = $e->getTrace();
        $prev = $e->getPrevious();
        $result[] = sprintf('%s%s: %s', $starter, get_class($e), $e->getMessage());
        $file = $e->getFile();
        $line = $e->getLine();
        while (true) {
            $current = "$file:$line";
            if (is_array($seen) && in_array($current, $seen)) {
                $result[] = sprintf(' ... %d more', count($trace) + 1);
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
     * used by mcx-lister
     *
     * installs exception handler which writes Timestamp + jTraceEx to STDERR
     * useful to see in error logs of supervisor when the error happens
     */
    public static function installExceptionHandler()
    {
        set_exception_handler(function (\Throwable $ex) {
            fwrite(STDERR, "\n\n------------------------ " . date("Y-m-d H:i:s") . " ------------------------\n\n");
            fwrite(STDERR, self::jTraceEx($ex) . "\n\n");
        });
    }


    /**
     * helper
     * @param $item
     * @return string
     */
    private static function _argumentToString($item)
    {
        if (is_string($item)) {
            return 's.' . $item;
        } elseif (is_object($item)) {
            return 'o.' . Util::getClassNameShort($item);
        } elseif (is_array($item)) {
            return 'a.' . count($item);
        } elseif (is_numeric($item)) {
            return 'n.' . strval($item);
        }

        return "x." . $item;
    }


    /**
     * @return string
     */
    public static function getDebugBacktraceAsText($skip=1, string $rootDirToRemove=null)
    {
        $stack = debug_backtrace();
        $output = '';

        $stackLen = count($stack);
        for ($i = $skip; $i < $stackLen; $i++) {
            $entry = $stack[$i];

            $func = empty($entry['class']) ? $entry['function'] : Util::removeNamespace($entry['class']) . '::' . $entry['function'];
            $func.= '(';
            $argsLen = count($entry['args']);
            for ($j = 0; $j < $argsLen; $j++) {
                $func .= self::_argumentToString($entry['args'][$j]);
                if ($j < $argsLen - 1) $func .= ', ';
            }
            $func .= ')';


            $entry_file = 'NO_FILE';
            if (array_key_exists('file', $entry)) {
                $entry_file = $entry['file'];
                if( $rootDirToRemove && UtilString::startsWith($entry_file, $rootDirToRemove)) {
                    $entry_file = substr($entry_file, strlen($rootDirToRemove));
                }
            }
            $entry_line = 'NO_LINE';
            if (array_key_exists('line', $entry)) {
                $entry_line = $entry['line'];
            }
            $output .= $i . ' - ' . $entry_file . ':' . $entry_line . ' - ' . $func . PHP_EOL;
        }
        return $output;
    }



}
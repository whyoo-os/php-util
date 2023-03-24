<?php


namespace WhyooOs\Util;

use SqlFormatter;

/**
 * 07/2017
 */
class UtilDebug
{


    private static $timeProfilers = [];


    /**
     * uses jdorn/sql-formatter
     * @param string $sql
     * @return String formatted query (html or ansi)
     */
    static function getSqlFormatted(string $sql)
    {
        return SqlFormatter::format($sql);
    }


    /**
     * dump
     */
    public static function d()
    {
        $ddSource = debug_backtrace()[0];
        echo basename($ddSource['file']) . ':' . $ddSource['line'] . Util::getNewline();
        foreach (func_get_args() as $arg) {
            dump($arg);
        }
    }


    /**
     * dump + die
     */
    public static function dd()
    {
        $ddSource = debug_backtrace()[0];
        echo basename($ddSource['file']) . ':' . $ddSource['line'] . Util::getNewline();
        foreach (func_get_args() as $arg) {
            dump($arg);
        }
        die();
    }


    /**
     * alias for DumpSql
     */
    public static function ds(string $sql)
    {
        self::dumpSql($sql);
    }

    /**
     * alias for DumpSqlDie
     */
    public static function dsd(string $sql)
    {
        self::dumpSqlDie($sql);
    }


    /**
     * @param string $id
     */
    public static function startTimeProfiling(string $id = 'default'): void
    {
        self::$timeProfilers[$id] = microtime(true);
    }

    /**
     * @param string $id
     * @return float seconds
     */
    public static function stopTimeProfiling(string $id = 'default'): float
    {
        $length = microtime(true) - self::$timeProfilers[$id];
        self::$timeProfilers[$id] = null;

        return $length;
    }

    /**
     * calls UtilSymfony::toArray to each parameter before dumping it
     *
     * @param 08/2018
     */
    public static function d2($mainCriteriaSet)
    {
        $ddSource = debug_backtrace()[0];
        echo $ddSource['file'] . ':' . $ddSource['line'] . Util::getNewline();
        foreach (func_get_args() as $arg) {
            dump(UtilSymfony::toArray($arg));
        }
    }


    /**
     * 01/2021 created
     *
     * used by algotrend
     *
     * composer require jdorn/sql-formatter
     *
     * @param string $sql some sql query
     */
    public static function dumpSql(string $sql)
    {
        $ddSource = debug_backtrace()[0];
        echo $ddSource['file'] . ':' . $ddSource['line'] . Util::getNewline();
        echo SqlFormatter::format($sql);
    }

    /**
     * 03/2023 created
     *
     * @param string $sql
     * @return void
     */
    private static function dumpSqlDie(string $sql)
    {
        self::dumpSql($sql);
        die();
    }

}

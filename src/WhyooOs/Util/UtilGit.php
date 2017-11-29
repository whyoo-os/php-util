<?php

/**
 * util class for fetching information about git repo
 */

namespace WhyooOs\Util;


class UtilGit
{
    /**
     * legacy
     */
    public static function version(string $path=null)
    {
        $paramPath = $path ? " -C $path " : '';
        exec("git $paramPath describe --always", $version_mini_hash);
        exec("git $paramPath rev-list HEAD | wc -l", $version_number);
        exec("git $paramPath log -1", $line);
        $version['short'] = "v1." . trim($version_number[0]) . "." . $version_mini_hash[0]; // ?? v1 ?? remove?
        $version['full'] = "v1." . trim($version_number[0]) . ".$version_mini_hash[0] (" . str_replace('commit ', '', $line[0]) . ")";; // ?? v1 ?? remove?
        // TODO: 2 functions for short and full hash (used for cache busting)
        $version['shortHash'] = exec("git $paramPath rev-parse --short HEAD");
        $version['fullHash'] = exec("git $paramPath rev-parse HEAD");

        return $version;
    }

    /**
     * used in shipping for "version number"
     */
    public static function getDate(string $path=null)
    {
        $paramPath = $path ? " -C $path " : '';
        return exec("git $paramPath log -1 --format=%cd --date=short");
    }

    /**
     * used by marketer
     * ideal for cache-buster
     * @return string
     */
    public static function getShortHash(string $path=null)
    {
        $paramPath = $path ? " -C $path " : '';
        return exec("git $paramPath rev-parse --short HEAD");
    }
}


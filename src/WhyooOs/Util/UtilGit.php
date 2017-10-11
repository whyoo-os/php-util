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
    public static function version()
    {
        exec('git describe --always', $version_mini_hash);
        exec('git rev-list HEAD | wc -l', $version_number);
        exec('git log -1', $line);
        $version['short'] = "v1." . trim($version_number[0]) . "." . $version_mini_hash[0]; // ?? v1 ?? remove?
        $version['full'] = "v1." . trim($version_number[0]) . ".$version_mini_hash[0] (" . str_replace('commit ', '', $line[0]) . ")"; ; // ?? v1 ?? remove?
        // TODO: 2 functions for short and full hash (used for cache busting)
        $version['shortHash'] = exec('git rev-parse --short HEAD');
        $version['fullHash'] = exec('git rev-parse HEAD');

        return $version;
    }

    /**
     * used in shipping for "version number"
     */
    public static function getDate()
    {
        return exec('git log -1 --format=%cd --date=short');
    }
}


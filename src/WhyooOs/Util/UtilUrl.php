<?php

namespace WhyooOs\Util;


/**
 * 01/2018 created
 */

class UtilUrl
{

    /**
     * TODO: use parse_url()
     *
     * @param $url
     * @return null
     */
    public static function getDomainFromUrl($url)
    {
        if (preg_match('#https?://([^/]+)$#i', $url, $matches)) {
            return $matches[1];
        }
        return null; // should NEVER happen
    }



    /**
     * @param $text
     * @return string[] the grepped urls
     */
    public static function grepUrls($text)
    {
        preg_match_all('#(https?|ftp)\:\/\/[a-z0-9\-\.\+\?\&\;\/\_\=]+#i', $text, $gr, PREG_PATTERN_ORDER);

        return $gr[0];
    }

    /**
     * 01/2018
     * @param $text
     * @return null
     */
    public static function grepFirstUrl($text)
    {
        if (preg_match('#(https?://[^\s]+?)$#i', $text, $matches)) {
            return $matches[1];
        }

        if (preg_match('#(https?://[^\s]+?)\s+#i', $text, $matches)) {
            return $matches[1];
        }

        return null;
    }



}

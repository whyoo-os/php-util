<?php

namespace WhyooOs\Util;

class UtilCurl
{
    public static $info;

    public static function curlGet($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1');
        curl_setopt($ch, CURLOPT_COOKIEJAR, "cookie_after.txt"); // upon completing request, curl saves/updates any cookies in this file
        curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies.txt"); // cookies in this file are sent by curl with the request
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $data = curl_exec($ch);
        self::$info = curl_getinfo($ch);

        return $data;
    }

}
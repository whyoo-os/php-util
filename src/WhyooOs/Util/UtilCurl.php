<?php

namespace WhyooOs\Util;

class UtilCurl
{
    public static $info;
    public static $lastWasCached;
    private static $lastCacheFile;

    /**
     * @param $url
     * @return mixed response body (eg html)
     */
    public static function curlGet($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1');
        curl_setopt($ch, CURLOPT_COOKIEJAR, "cookie_after.txt"); // upon completing request, curl saves/updates any cookies in this file
        curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies.txt"); // cookies in this file are sent by curl with the request
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
// 		curl_setopt ($ch, CURLOPT_VERBOSE, 0);
// 		curl_setopt ($ch, CURLOPT_HEADER, 0);
// 		curl_setopt ($ch, CURLINFO_HEADER_OUT,true);
// 		curl_setopt ($ch, CURLOPT_USERAGENT, $uagentutilitzat);
//      curl_setopt($ch, CURLOPT_COOKIESESSION, TRUE);
        $data = curl_exec($ch);
        self::$info = curl_getinfo($ch);

        return $data;
    }




    /**
     * @param $url
     * @param int $cacheTTL TTL in seconds
     * @return mixed
     */
    public static function curlGetCached($url, $cacheTTL = 3600)
    {
        $pathCache = __DIR__ . '/curl_cache/' . md5($url);
        if (file_exists($pathCache) && time() - filemtime($pathCache) < $cacheTTL) {
            self::$lastWasCached = true;
            return file_get_contents($pathCache);
        }
        $content = self::curlGet($url);
        file_put_contents($pathCache, $content);
        self::$lastWasCached = false;
        self::$lastCacheFile = $pathCache;


        // dump(self::$info);
        if( self::$info['http_code'] != 200) {
            return false;
        }

        return $content;
    }

//
//    function curlPost( $url, array $postData)
//    {
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1');
//        curl_setopt($ch, CURLOPT_URL, $url);
//        curl_setopt($ch, CURLOPT_POST, 1);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query( $postData));
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // receive server response ...
//        $server_output = curl_exec ($ch);
//        curl_close ($ch);
//
//        return  $server_output;
//    }
//

    /**
     * @param $url
     * @param array $fields
     * @return mixed
     */
    public static function curlPost($url, array $fields)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            print curl_error($ch);
        } else {
            curl_close($ch);
        }
        return $result;
    }

    public static function removeLastCached()
    {
        @unlink(self::$lastCacheFile);
    }



}
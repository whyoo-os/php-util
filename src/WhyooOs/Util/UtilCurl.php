<?php

namespace WhyooOs\Util;

class UtilCurl
{
    public static $info;
    public static $lastWasCached;
    public static $lastCacheFile;
    public static $pathCacheDir = '/tmp/curl_cache';




    /**
     * @param $url
     * @return mixed response body (eg html)
     */
    public static function curlGet($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1');
//        curl_setopt($ch, CURLOPT_COOKIEJAR, "cookie_after.txt"); // upon completing request, curl saves/updates any cookies in this file
//        curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies.txt"); // cookies in this file are sent by curl with the request
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
        $pathCache = self::$pathCacheDir . '/' . md5($url);
        if (file_exists($pathCache) && time() - filemtime($pathCache) < $cacheTTL) {
            self::$lastWasCached = true;
            self::$lastCacheFile = $pathCache;
            return file_get_contents($pathCache);
        }
        $content = self::curlGet($url);
        UtilFilesystem::mkdirIfNotExists(self::$pathCacheDir);
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




// from marketer
    public static function curlPostCached($url, array $fields, $cacheTTL=null)
    {
        $pathCache = self::$pathCacheDir . '/' . md5($url.serialize($fields));
        if (file_exists($pathCache) && ((time() - filemtime($pathCache) < $cacheTTL) || is_null($cacheTTL))) {
            self::$info = "from cache $pathCache";
            self::$lastWasCached = true;
            self::$lastCacheFile = $pathCache;
            UtilFilesystem::mkdirIfNotExists(self::$pathCacheDir);
            return file_get_contents($pathCache);
        }
        self::$lastCacheFile = $pathCache;
        self::$lastWasCached = false;
        $content = self::curlPost($url, $fields);
        file_put_contents($pathCache, $content);

        return $content;
    }


// from marketer
    public static function isValidUrl($url)
    {
        // first do some quick sanity checks:
        if (!$url || !is_string($url)) {
            return false;
        }
        // quick check url is roughly a valid http request: ( http://blah/... )
        if (!preg_match('/^http(s)?:\/\/[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(\/.*)?$/i', $url)) {
            return false;
        }
        // the next bit could be slow:
        if (self::getHttpResponseCode($url) != 200) {
            return false;
        }
        // all good!
        return true;
    }


// from marketer
    private static function getHttpResponseCode($url, $followredirects = true)
    {
        // returns int responsecode, or false (if url does not exist or connection timeout occurs)
        // NOTE: could potentially take up to 0-30 seconds , blocking further code execution (more or less depending on connection, target site, and local timeout settings))
        // if $followredirects == false: return the FIRST known httpcode (ignore redirects)
        // if $followredirects == true : return the LAST  known httpcode (when redirected)
        if (!$url || !is_string($url)) {
            return false;
        }
        $ch = @curl_init($url);
        if ($ch === false) {
            return false;
        }
        @curl_setopt($ch, CURLOPT_HEADER, true);    // we want headers
        @curl_setopt($ch, CURLOPT_NOBODY, true);    // dont need body
        @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    // catch output (do NOT print!)
        if ($followredirects) {
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            @curl_setopt($ch, CURLOPT_MAXREDIRS, 10);  // fairly random number, but could prevent unwanted endless redirects with followlocation=true
        } else {
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        }
//      @curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,5);   // fairly random number (seconds)... but could prevent waiting forever to get a result
//      @curl_setopt($ch, CURLOPT_TIMEOUT        ,6);   // fairly random number (seconds)... but could prevent waiting forever to get a result
//      @curl_setopt($ch, CURLOPT_USERAGENT      ,"Mozilla/5.0 (Windows NT 6.0) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.89 Safari/537.1");   // pretend we're a regular browser
        @curl_exec($ch);
        if (@curl_errno($ch)) {   // should be 0
            @curl_close($ch);
            return false;
        }
        $code = @curl_getinfo($ch, CURLINFO_HTTP_CODE); // note: php.net documentation shows this returns a string, but really it returns an int
        @curl_close($ch);
        return $code;
    }





    public static function removeLastCached()
    {
        @unlink(self::$lastCacheFile);
    }



}
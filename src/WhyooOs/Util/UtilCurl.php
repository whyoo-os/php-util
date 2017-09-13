<?php
/**
 * 09/2017 version from scrapers
 *
 * Utility class for quickly fetching urls ... uses phpfastcache[sqlite] for caching
 */

namespace WhyooOs\Util;

use phpFastCache\CacheManager;

class UtilCurl
{
    private static $cacheManager;
    private static $cacheDefaultTTL;
    private static $lastCacheKey; // for removing failed downloads from cache

    public static $info;
    public static $fromCache;
    public static $pathCache;
    public static $headers;
    public static $pathCookiesTxt;


    public static $cacheConfig = [
        'ignoreSymfonyNotice' => true, // Ignore Symfony notices for Symfony projects that do not makes use of PhpFastCache's Symfony Bundle (?)
        'path' => 'TODO_____XXXX',
    ];


    protected static function _getCacheManager()
    {
        if (is_null(self::$cacheManager)) {
            CacheManager::setDefaultConfig(UtilCurl::$cacheConfig);
            self::$cacheManager = CacheManager::getInstance('sqlite');
        }
        return self::$cacheManager;
    }


    /**
     * @param $url
     * @param null $cacheTTL
     * @return mixed|string
     */
    public static function curlGetCached($url, $cacheTTL = null)
    {
        if (is_null($cacheTTL)) {
            $cacheTTL = self::$cacheDefaultTTL;
        }

        $cacheManager = self::_getCacheManager();
        $cachekey = "GET " . $url;
        $cacheItem = $cacheManager->getItem($cachekey);
        if ($cacheItem->isHit()) {
            self::$info = "from cache_old";
            self::$fromCache = true;
            return $cacheItem->get();
        } else {
            self::$fromCache = false;
            self::$lastCacheKey = $cachekey;
            $content = self::curlGet($url);
            $cacheItem->set($content);
            $cacheItem->expiresAfter($cacheTTL);
            $cacheManager->save($cacheItem);
            return $content;
        }
    }

    /**
     * @param $url
     * @param null $cacheTTL
     * @return mixed|string
     */
    public static function curlPostCached($url, array $fields = [], $cacheTTL = null)
    {
        if (is_null($cacheTTL)) {
            $cacheTTL = self::$cacheDefaultTTL;
        }

        $cacheManager = self::_getCacheManager();
        $cachekey = "POST " . $url . " " . json_encode($fields);
        $cacheItem = $cacheManager->getItem($cachekey);
        if ($cacheItem->isHit()) {
            self::$info = "from cache_old";
            self::$fromCache = true;
            return $cacheItem->get();
        } else {
            self::$fromCache = false;
            self::$lastCacheKey = $cachekey;
            $content = self::curlPost($url, $fields);
            $cacheItem->set($content);
            $cacheItem->expiresAfter($cacheTTL);
            $cacheManager->save($cacheItem);
            return $content;
        }
    }


    /**
     * legacy from scraper
     *
     * @param $url
     * @param array $fields
     * @param null $cacheTTL
     * @return bool|mixed|string
     */
    public static function curlPostCachedOld($url, array $fields, $cacheTTL = null)
    {
        $pathCache = __DIR__ . '/curl_cache_old/' . md5($url . serialize($fields));
        if (file_exists($pathCache) && ((time() - filemtime($pathCache) < $cacheTTL) || is_null($cacheTTL))) {
            self::$info = "from cach $pathCache";
            self::$fromCache = true;
            self::$pathCache = $pathCache;
            return file_get_contents($pathCache);
        }
        self::$pathCache = null;
        self::$fromCache = false;
        $content = self::curlPost($url, $fields);
        file_put_contents($pathCache, $content);
        return $content;
    }


    /*
        public static function curlGetCachedOld($url, $cacheTTL = null)
        {
            $pathCache = __DIR__ . '/curl_cache_old/' . md5($url);
            if (file_exists($pathCache) && ((time() - filemtime($pathCache) < $cacheTTL) || is_null($cacheTTL))) {
                $cached = file_get_contents($pathCache);
                self::$info = "from cache_old";
                self::$fromCache = true;
                self::$pathCache = $pathCache;
                return $cached;
            }
            self::$pathCache = null;
            self::$fromCache = false;
            $content = self::curlGet($url);
            file_put_contents($pathCache, $content);
            return $content;
        }
    */


    private static function _initCurl($ch)
    {
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1');
        curl_setopt($ch, CURLOPT_COOKIEJAR, self::$pathCookiesTxt); // upon completing request, curl saves/updates any cookies in this file
        curl_setopt($ch, CURLOPT_COOKIEFILE, self::$pathCookiesTxt); // cookies in this file are sent by curl with the request
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
        curl_setopt($ch, CURLOPT_HTTPHEADER, self::$headers);
    }


    /**
     * @param $url
     * @return mixed
     */
    public static function curlGet($url)
    {
        $ch = curl_init($url);
        self::_initCurl($ch);

// 		curl_setopt ($ch, CURLOPT_VERBOSE, 0);
// 		curl_setopt ($ch, CURLOPT_HEADER, 0);
// 		curl_setopt ($ch, CURLINFO_HEADER_OUT,true);
// 		curl_setopt ($ch, CURLOPT_USERAGENT, $uagentutilitzat);
// #		curl_setopt($ch, CURLOPT_COOKIESESSION, TRUE);

        $data = curl_exec($ch);
        self::$info = curl_getinfo($ch);
        return $data;
    }


    public static function curlPost($url, array $fields = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        self::_initCurl($ch);

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            print curl_error($ch);
        } else {
            curl_close($ch);
        }
        return $result;
    }


    public static function addHeader($header)
    {
        self::$headers[] = $header;
    }

    public static function setCachePath($path)
    {
        self::$cacheConfig['path'] = $path;
    }

    public static function setCacheDefaultLifetime($ttl)
    {
        self::$cacheDefaultTTL = $ttl;
    }


    public static function removeLastFromCache()
    {
        self::_getCacheManager()->deleteItem(self::$lastCacheKey);
    }


    public static function setCookiesPath($path)
    {
        self::$pathCookiesTxt = $path;
    }

    public static function removePostFromCache($url, $fields)
    {
        $cachekey = "POST " . $url . " " . json_encode($fields);
        self::_getCacheManager()->deleteItem($cachekey);
    }

    public static function deleteCookies()
    {
        @unlink(self::$pathCookiesTxt);
    }

    public static function clearCookies()
    {
        @unlink(self::$pathCookiesTxt);
    }

}

UtilCurl::setCachePath(__DIR__ . '/curl_cache');
UtilCurl::setCacheDefaultLifetime(3600 * 10); // 10h
UtilCurl::$headers = [];
UtilCurl::$pathCookiesTxt = __DIR__ . "/cookies.txt";

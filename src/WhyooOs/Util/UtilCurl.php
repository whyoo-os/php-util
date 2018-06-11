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
    private static $numRequests = 0;


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
        self::$lastCacheKey = self::_getCacheKeyGet($url);
        $cacheItem = $cacheManager->getItem(self::$lastCacheKey);
        if ($cacheItem->isHit()) {
            self::$info = "from cache_old";
            self::$fromCache = true;
            return $cacheItem->get();
        } else {
            self::$fromCache = false;
            $content = self::curlGet($url);
            $cacheItem->set($content);
            $cacheItem->expiresAfter($cacheTTL);
            $cacheManager->save($cacheItem);
            return $content;
        }
    }

    /**
     * convenience function
     *
     * @param $url
     * @param null $cacheTTL
     * @param int $sleep
     * @return mixed|string
     */
    public static function curlGetCachedThrottled($url, $cacheTTL = null, $sleep = 10)
    {
        if (!self::wasLastRequestCached() && self::$numRequests > 0) {
            sleep($sleep);
        }
        return self::curlGetCached($url, $cacheTTL);
    }


    /**
     * convenience function
     * used by marketer
     * 01/2018
     *
     * @param $url
     * @param null $cacheTTL
     */
    public static function curlGetJsonCached($url, $cacheTTL = null)
    {
        return json_decode(self::curlGetCached($url, $cacheTTL), true);
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
        self::$lastCacheKey = self::_getCacheKeyPost($url, $fields);
        $cacheItem = $cacheManager->getItem(self::$lastCacheKey);
        if ($cacheItem->isHit()) {
            self::$info = "from cache_old";
            self::$fromCache = true;
            return $cacheItem->get();
        } else {
            self::$fromCache = false;
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
        } else {
            self::$pathCache = null;
            self::$fromCache = false;
            $content = self::curlPost($url, $fields);
            file_put_contents($pathCache, $content);

            return $content;
        }
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
        $options = [
            CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/59.0.3071.109 Chrome/59.0.3071.109 Safari/537.36',
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_FOLLOWLOCATION => true,     // follow redirects
            CURLOPT_ENCODING => "gzip, deflate",       // "" means "handle all encodings" ?
            CURLOPT_AUTOREFERER => true,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
            CURLOPT_TIMEOUT => 120,      // timeout on response
            CURLOPT_MAXREDIRS => 10,       // stop after 10 redirects
            CURLINFO_HEADER_OUT => true,
            CURLOPT_SSL_VERIFYPEER => false,     // Disabled SSL Cert checks
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER => self::$headers,


            CURLOPT_VERBOSE => false,
            CURLOPT_HEADER => false,     // return headers in addition to content


            // cookie stuff
            CURLOPT_COOKIESESSION => true,
            CURLOPT_COOKIEFILE => self::$pathCookiesTxt, // cookies in this file are sent by curl with the request
            CURLOPT_COOKIEJAR => self::$pathCookiesTxt,// upon completing request, curl saves/updates any cookies in this file
            CURLOPT_COOKIE => self::$pathCookiesTxt, // ????
        ];

        curl_setopt_array($ch, $options);
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
        self::$numRequests++;

        self::$info = curl_getinfo($ch);
        return $data;
    }


    public static function curlPost($url, array $fields = [])
    {
        $ch = curl_init();
        self::_initCurl($ch);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        curl_setopt($ch, CURLOPT_STDERR, fopen('/tmp/request.txt', 'w'));

        $result = curl_exec($ch);
        self::$numRequests++;

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

    private static function _getCacheKeyPost($url, $fields)
    {
        return md5("POST " . $url . " " . json_encode($fields));
    }

    private static function _getCacheKeyGet($url)
    {
        return md5("GET " . $url);
    }

    public static function removePostFromCache($url, $fields)
    {
        self::_getCacheManager()->deleteItem(self::_getCacheKeyPost($url, $fields));
    }

    public static function removeGetFromCache($url)
    {
        self::_getCacheManager()->deleteItem(self::_getCacheKeyGet($url));
    }

    public static function deleteCookies()
    {
        @unlink(self::$pathCookiesTxt);
    }

    public static function clearCookies()
    {
        @unlink(self::$pathCookiesTxt);
    }

    /**
     * we use this to decide if we need to sleep a while until we make next request
     * @return mixed
     */
    public static function wasLastRequestCached()
    {
        return self::$fromCache;
    }

}

UtilCurl::setCachePath(dirname(Util::getCallingScript()) . '/curl_cache');
UtilCurl::setCacheDefaultLifetime(3600 * 10); // 10h
UtilCurl::$headers = [];
UtilCurl::$pathCookiesTxt = dirname(Util::getCallingScript()) . '/cookies.txt';

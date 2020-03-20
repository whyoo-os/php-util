<?php
/**
 * 09/2017
 */


namespace WhyooOs\Util;

use KubAT\PhpSimple\HtmlDomParser;

class UtilScraper
{

    public static $dom;

    /** @var array */
    private static $lastRequestAt = [];


    /**
     * 09/2017 helper
     *
     * @param $html
     * @return mixed
     */
    private static function br2nl($html)
    {
        return preg_replace('#<br\s*/?>#i', "\n", $html);
    }


    /**
     * extract single information from loaded dom
     *
     * @param string $expression
     * @param string $attributeName
     * @return string
     */
    public static function extractStringFromDom(string $expression, string $attributeName = 'innertext')
    {
        $text = self::$dom->find($expression, 0)->$attributeName;

        return self::rectifyScrapedText($text);
    }

    /**
     * extract multiple information from loaded dom (a list)
     *
     * @param string $expression
     * @param string $attributeName
     * @return string[]
     */
    public static function extractManyStringsFromDom(string $expression, string $attributeName = 'innertext')
    {
        $list = [];
        foreach (self::$dom->find($expression) as $el) {
            $list[] = self::rectifyScrapedText($el->$attributeName);
        }

        return $list;
    }


    /**
     * @param $html
     */
    public static function loadDom($html)
    {
        self::$dom = HtmlDomParser::str_get_html($html);
    }

    /**
     * @param string $text
     * @return string
     */
    public static function rectifyScrapedText($text)
    {
        if (!is_string($text)) {
            return $text;
        }
        $text = html_entity_decode($text, ENT_QUOTES);
        $text = preg_replace('#\s+#', ' ', $text);
        $text = self::br2nl($text);
        $text = strip_tags($text);

        return trim($text);
    }

    /**
     * 11/2017 ebay
     * rectifies links like '//somedomain.com/abc.html'
     *
     * @param $link
     * @param string $protocol
     * @return string
     */
    public static function rectifyLink(string $link, string $protocol = 'https')
    {
        if (strpos($link, '//') === 0) {
            return $protocol . ':' . $link;
        }

        return $link;
    }




    /**
     * 09/2018
     *
     * for throttling requests
     *
     * @param int $milliSeconds
     * @param string $id
     */
    public static function wait(int $milliSeconds, $id = 'default')
    {
        $seconds = $milliSeconds / 1000.0;

        if (!key_exists($id, self::$lastRequestAt)) {
            self::$lastRequestAt[$id] = microtime(true);
            return;
        }

        while (microtime(true) - self::$lastRequestAt[$id] < $seconds) {
            // sleep 100ths of max delay 
            sleep($seconds / 100);
        }

        self::$lastRequestAt[$id] = microtime(true);
    }

    /**
     * 03/2020
     *
     * used in scraper-service
     * returns only positive integers
     *
     * @param string $param
     * @return array with the positive integer numbers
     */
    public static function parseDecimals(string $string=null)
    {
        preg_match_all('~\d+~', $string, $matches, PREG_PATTERN_ORDER);

        return $matches[0];
    }


}

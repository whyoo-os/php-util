<?php
/**
 * 09/2017
 */


namespace WhyooOs\Util;

use Sunra\PhpSimple\HtmlDomParser;


class UtilScraper
{

    public static $dom;

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
    public static function extractStringFromDom(string $expression, string $attributeName='innertext')
    {
        $text = self::$dom->find($expression, 0)->$attributeName;

        return self::rectifyScrapedText($text);
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
        if( !is_string($text)) {
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
    public static function rectifyLink(string $link, string $protocol='https')
    {
        if( strpos($link, '//') === 0) {
            return $protocol . ':' . $link;
        }

        return $link;
    }



}

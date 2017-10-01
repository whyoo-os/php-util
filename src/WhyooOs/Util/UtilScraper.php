<?php
/**
 * 09/2017
 */


namespace WhyooOs\Util;

include( __DIR__ . '/../HelperClasses/simple_html_dom.php');

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
     * @param $expression
     * @return string
     */
    public static function extractStringFromDom($expression)
    {
        $text = self::$dom->find($expression, 0)->innertext;

        // rectify
        $text = html_entity_decode($text, ENT_HTML5);
        $text = preg_replace('#\s+#', ' ', $text);
        $text = self::br2nl($text);
        $text = strip_tags($text);

        return trim($text);
    }

    /**
     * @param $html
     */
    public static function loadDom($html)
    {
        self::$dom = str_get_html($html);
    }


}

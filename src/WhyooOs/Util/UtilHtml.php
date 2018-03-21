<?php

namespace WhyooOs\Util;


class UtilHtml
{

    /**
     * 09/2017 ebaygen - used for emails
     *
     * extracts <style> css and converts to inline style
     * TODO: use shippingplatform's UtilMail::cssToInline instead!
     *
     * @param $bodyHtml
     * @return string
     */
    public static function cssToInline(string $bodyHtml)
    {
//        $parser = $this->container->get('templating.name_parser');
//        $locator = $this->container->get('templating.locator');
//        $pathCss = $locator->locate($parser->parse('AcmeProjectBundle::home.html.twig'));


//        $pathCss = $this->webRoot . '/bower_components/bootstrap/dist/css/bootstrap.css';
//        $css = file_get_contents($pathCss); // "td { color: blue; }";

        // get <style> content
        preg_match_all('#<style>(.*?)</style>#si', $bodyHtml, $matches, PREG_PATTERN_ORDER);
        // remove <style>
        $bodyHtml = preg_replace('#<style>.*?</style>#si', '', $bodyHtml);
        $css = implode("\n", $matches[1]);
        $cssToInlineStyles = new \TijsVerkoyen\CssToInlineStyles\CssToInlineStyles();
        $bodyHtml = $cssToInlineStyles->convert($bodyHtml, $css);

        return $bodyHtml;
    }


    /**
     * 03/2018 used for marketer
     */
    public static function replaceHtmlTags($text)
    {
        return preg_replace_callback(
            '#<.*?>#', function ($m) {
            return '{{{{{{{{{{' . base64_encode($m[0]) . '}}}}}}}}}}';
        }, $text);
    }


    /**
     * 03/2018 used for marketer
     */
    public static function unreplaceHtmlTags($text)
    {
        return preg_replace_callback(
            '#{{{{{{{{{{(.*?)}}}}}}}}}}#', function ($m) {
            return base64_decode($m[1]);
        }, $text);
    }


}

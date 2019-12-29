<?php

namespace WhyooOs\Util;


use tidy;

class UtilHtml
{
    /**
     * 09/2017 mcxlister - used for emails
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
            '#\{\{\{\{\{\{\{\{\{\{(.*?)\}\}\}\}\}\}\}\}\}\}#', function ($m) {
            return base64_decode($m[1]);
        }, $text);
    }


    /**
     * adds <a> tags for urls in text
     * also (optionally) trims the text to $maxLength characters (html tags are ignored)
     * if text was truncated, self::$textWasTruncated is set to true, false otherwise
     *
     * 08/2018 moved from WallPostEnhancer to here, added $maxLength,
     * 08/2018 renamed from linkUrls to linkifyAndTruncateText
     *
     * @param string $text
     * @param int $maxLength
     * @return array [htmlWithLinksClickable, $textWasTruncated)
     */
    public static function linkifyAndTruncateText(string $text = null, int $maxLength = 0)
    {
        // 1) replace html tags to avoid replacements inside of html tags
        $text = self::replaceHtmlTags($text);

        // ---- if we need to trim we do it now.
        $bNeedCut = false;
        if ($maxLength) {
            $text = preg_replace('!\s+!', ' ', $text);
            $parts = explode(' ', $text);
            $textLength = 0;
            foreach ($parts as $idxWord => $part) {

                # remove encoded html tags because we do not count their text-length
                $part = preg_replace('#\{\{\{\{\{\{\{\{\{\{(.*?)\}\}\}\}\}\}\}\}\}}#', '', $part);
                $partLength = mb_strlen($part);

                if ($partLength > 0) {
                    $textLength += $partLength;

                    # echo "xxx $textLength\n";
                    if ($textLength > $maxLength) {
                        $textLength -= mb_strlen($part);
                        # echo "---break\n";
                        $bNeedCut = true;
                        break;
                    }
                    $textLength += 1;
                }
            }
            // ---- if needed: do actual cut and close open Tags
            if ($bNeedCut) {
#                UtilDebug::d(self::unreplaceHtmlTags($parts[0]));
                $text = implode(' ', array_slice($parts, 0, $idxWord));
            }
        }

        // 2) convert-plain-text-urls-into-html-hyperlink (source: http://stackoverflow.com/questions/1960461/convert-plain-text-urls-into-html-hyperlinks-in-php)
        $text = preg_replace('@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\{\s])?)?)@', '<a class="wall-link" href="$1" target="_blank">$1</a>', $text);




        // 3) replace back the html tags
        $text = self::unreplaceHtmlTags($text);


        // ---- close open tags
        if ($bNeedCut) {
            $text = self::closeOpenTags($text);
        }

        return [$text, $bNeedCut];
    }


    /**
     * 08/2018
     * source: https://stackoverflow.com/a/3810341/2848530
     * uses tidy http://php.net/manual/en/book.tidy.php
     *
     * @param string $text
     * @return string cleaned html
     */
    private static function closeOpenTags(string $text)
    {
//  "indent-spaces" => 2
//  "wrap" => 68
//  "tab-size" => 8
//  "char-encoding" => 1
//  "input-encoding" => 3
//  "output-encoding" => 1
//  "newline" => 0
//  "doctype-mode" => 1
//  "doctype" => ""
//  "repeated-attributes" => 1
//  "alt-text" => ""
//  "slide-style" => ""
//  "error-file" => ""
//  "output-file" => ""
//  "write-back" => false
//  "markup" => true
//  "show-warnings" => true
//  "quiet" => false
//  "indent" => 0
//  "hide-endtags" => false
//  "input-xml" => false
//  "output-xml" => false
//  "output-xhtml" => false
//  "output-html" => false
//  "add-xml-decl" => false
//  "uppercase-tags" => false
//  "uppercase-attributes" => false
//  "bare" => false
//  "clean" => false
//  "logical-emphasis" => false
//  "drop-proprietary-attributes" => false
//  "drop-font-tags" => false
//  "drop-empty-paras" => true
//  "fix-bad-comments" => true
//  "break-before-br" => false
//  "split" => false
//  "numeric-entities" => false
//  "quote-marks" => false
//  "quote-nbsp" => true
//  "quote-ampersand" => true
//  "wrap-attributes" => false
//  "wrap-script-literals" => false
//  "wrap-sections" => true
//  "wrap-asp" => true
//  "wrap-jste" => true
//  "wrap-php" => true
//  "fix-backslash" => true
//  "indent-attributes" => false
//  "assume-xml-procins" => false
//  "add-xml-space" => false
//  "enclose-text" => false
//  "enclose-block-text" => false
//  "keep-time" => false
//  "word-2000" => false
//  "tidy-mark" => false
//  "gnu-emacs" => false
//  "gnu-emacs-file" => ""
//  "literal-attributes" => false
//  "show-body-only" => 0
//  "fix-uri" => true
//  "lower-literals" => true
//  "hide-comments" => false
//  "indent-cdata" => false
//  "force-output" => true
//  "show-errors" => 6
//  "ascii-chars" => false
//  "join-classes" => false
//  "join-styles" => true
//  "escape-cdata" => false
//  "language" => ""
//  "ncr" => true
//  "output-bom" => 2
//  "replace-color" => false
//  "css-prefix" => ""
//  "new-inline-tags" => ""
//  "new-blocklevel-tags" => ""
//  "new-empty-tags" => ""
//  "new-pre-tags" => ""
//  "accessibility-check" => 0
//  "vertical-space" => false
//  "punctuation-wrap" => false
//  "merge-divs" => 2
//  "decorate-inferred-ul" => false
//  "preserve-entities" => false
//  "sort-attributes" => 0
//  "merge-spans" => 2
//  "anchor-as-name" => true


        #return $text;

        $tidy = new Tidy();
        // workaround about issue with strings like "A <b>B"
        $wrapperOpen = '<xxx>';
        $wrapperClose = '</xxx>';
#        $wrapped = $wrapperOpen . $text . $wrapperClose;
        $clean = $tidy->repairString($text, [
//            'output-xml' => true,
//            'input-xml' => true,
        'show-body-only' => true,
            'input-encoding'=> 'utf8',
            'output-encoding'=> 'utf8',
            'preserve-entities' => true,
//            'newline' => 1,
        ]);
return $clean;
//
//        $withoutWrapper = substr($clean, strlen($wrapperOpen), strlen($clean) - strlen($wrapperOpen) - strlen($wrapperClose));
//
//        return $withoutWrapper;
    }


}

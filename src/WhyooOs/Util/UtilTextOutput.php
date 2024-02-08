<?php


namespace WhyooOs\Util;

/**
 * 02/2024 created
 */
class UtilTextOutput
{


    /**
     * 01/2018 created
     * 02/2024 moved from Util to UtilTextOutput
     */
    public static function isCLi(): bool
    {
        return php_sapi_name() == "cli";
    }


    /**
     * @return string \n or <br>
     */
    public static function getNewline(): string
    {
        if (self::isCli()) {
            // In cli-mode
            return "\n";
        } else {
            return '<br>';
        }
    }

    /**
     * 02/2024 created
     *
     * @param string $str
     * @return string
     */
    public static function escapeStringForHtml(string $str): string
    {
        if(self::isCLi()) {
            return $str;
        } else {
            return htmlspecialchars($str);
        }
    }



}
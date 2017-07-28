<?php


namespace WhyooOs\Util;

use SqlFormatter;

/**
 * 07/2017
 */
class UtilLegacy
{


    // from marketer v1
    public static function dbg($msg)
    {
        if (is_string($msg)) {
            echo "<div style=\"border:1px solid green\">$msg</div>";
        } else {
            echo "<div style=\"border:1px solid green\">";
            var_dump($msg);
            echo "</div>";
        }
    }





    /**
     *
     */
    public static function redirect($url, $bJavascript = false)
    {
        if ($bJavascript) {
            echo "<script type=\"text/javascript\">window.location='$url'</script>";
        } else {
            header("location: $url");
        }
        exit();
    }


    /**
     *
     */
    public static function redirectToReferer()
    {
        self::redirect($_SERVER['HTTP_REFERER']);
    }


    /**
     * @param $email
     * @return bool
     */
    public static function isValidEmail($email)
    {
        return (bool)preg_match("/^[a-zA-Z0-9\-\_\.]+@[a-zA-Z0-9\-\_\.]+\.[a-zA-Z]{2,6}$/", $email, $gr);
    }




}
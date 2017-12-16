<?php


namespace WhyooOs\Util;

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



    /**
     * from old legacy code
     *
     * @param $errorCode
     * @return mixed
     */
    static function	uploadErrorCodeToText( $errorCode)
    {
        define("UPLOAD_ERR_EMPTY",5);
        #   if($file['size'] == 0 && $file['error'] == 0)
        #   {
        #     $file['error'] = 5;
        #   }
        $upload_errors = array(
            UPLOAD_ERR_OK        => "No errors.",
            UPLOAD_ERR_INI_SIZE    => "Larger than upload_max_filesize.",
            UPLOAD_ERR_FORM_SIZE    => "Larger than form MAX_FILE_SIZE.",
            UPLOAD_ERR_PARTIAL    => "Partial upload.",
            UPLOAD_ERR_NO_FILE        => "No file.",
            UPLOAD_ERR_NO_TMP_DIR    => "No temporary directory.",
            UPLOAD_ERR_CANT_WRITE    => "Can't write to disk.",
            UPLOAD_ERR_EXTENSION     => "File upload stopped by extension.",
            UPLOAD_ERR_EMPTY        => "File is empty." // add this to avoid an offset
        );
        // error: report what PHP says went wrong
        return $upload_errors[$errorCode];
    }




}
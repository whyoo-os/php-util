<?php

namespace WhyooOs\Util;

class UtilRandom
{
    public static function createRandomString($length)
    {
        $initVector = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
        $rectified = preg_replace_callback("#[^a-zA-Z0-9]{1}#",
            function ($x) {
                return md5(time())[0];
            }, base64_encode($initVector));

        return substr($rectified, 0, $length);
    }

//    // alternative version from smartdonation
//    public static function createRandomString($length)
//    {
//        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
//        $randomString = '';
//        for ($i = 0; $i < $length; $i++) {
//            $randomString .= $characters[mt_rand(0, strlen($characters) - 1)];
//        }
//        return $randomString;
//    }


}

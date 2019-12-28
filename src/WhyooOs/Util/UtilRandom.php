<?php

namespace WhyooOs\Util;

class UtilRandom
{
    /**
     * @param $length
     * @return bool|string
     * @throws \Exception
     */
    public static function createRandomString($length)
    {
        // $initVector = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
        $initVector = random_bytes($length);
        $rectified = preg_replace_callback("#[^a-zA-Z0-9]{1}#",
            function ($x) {
                return md5(time())[0];
            }, base64_encode($initVector));

        return substr($rectified, 0, $length);
    }

    /**
     * 12/2017 - returns random float between 0 and 1
     * @return float|int
     */
    public static function getRandom01()
    {
        return mt_rand() / mt_getrandmax();
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


    /**
     * used to get random indexes
     *
     * @param $numIntegers
     */
    public static function getRandomIntegers(int $min, int $max, int $numIntegers)
    {
        $numbers = range($min, $max);
        shuffle($numbers);

        return array_slice($numbers, 0, $numIntegers);
    }


}

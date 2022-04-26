<?php

namespace WhyooOs\Util;

/**
 * used by SlidesMailer
 * used by Eqipoo Fixtures
 */
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
     * return int[] array with integers
     */
    public static function getRandomIntegers(int $min, int $max, int $numIntegers)
    {
        $numbers = range($min, $max);
        shuffle($numbers);

        return array_slice($numbers, 0, $numIntegers);
    }


    /**
     * Gausskurve mit Flaeche unter Graphen == 1
     * https://en.wikipedia.org/wiki/Normal_distribution#/media/File:Normal_Distribution_PDF.svg
     *
     * 01/2020 added, used as helper for biased random integer
     *
     * @param float $x
     * @param float $mu marks the position of the central peak of the bell curve
     * @param float $sigma determines its width (a larger value for $sigma means a broader distribution)
     * @return float|int
     */
    public static function probabilityDensityFunction($x, $mu, $sigma)
    {
        return exp(-0.5 * ($x - $mu) * ($x - $mu) / ($sigma * $sigma))
            / ($sigma * sqrt(2.0 * M_PI));
    }


    /**
     * normalisiert die rechte Seite einer Normalverteilungskurve [0..$MAX_X],
     * anschliessend wird die Funktion auf eine Zufallszahl zwischen 0 und $MAX_X angewendet
     *
     * 02/2020
     *
     * used in slides mailer
     *
     * @param int $min
     * @param int $max
     * @param int $numIntegers
     * @param float $sigma
     * @param float $MAX_X
     * return int[] array with integers
     */
    public static function getBiasedRandomIntegers(int $min, int $max, int $numIntegers, $sigma=1.0, $MAX_X=3)
    {

        $maxY = self::probabilityDensityFunction(0, 0, $sigma); // center of bell curve
        $minY = self::probabilityDensityFunction($MAX_X, 0, $sigma);

        UtilAssert::assertTrue($max - $min >= $numIntegers);
        $ret = [];
        while (count($ret) < $numIntegers) {
            $rand01 = self::getRandom01();
            // $rand01 = 1.0;
            $y = self::probabilityDensityFunction($rand01 * $MAX_X, 0, $sigma);
            $y01 = UtilNumber::normalize($y, $minY, $maxY);
            # UtilDebug::d($minY, $maxY, "----------");
            $n = (int)round($min + ($max - $min) * $y01);
            $ret[] = $n;
            $ret = array_unique($ret);
        }

        return $ret;
    }

    /**
     * 08/2020 moved from UtilArray to UtilRandom
     *
     * @param array $array
     * @param int $count
     * @return array
     */
    public static function getRandomElements($array, $count)
    {
        if ($count == 0) {
            return [];
        }
        if ($count > count($array)) {
            $count = count($array);
        }
        $indexes = array_rand($array, $count);

        if ($count == 1) { // force array
            $indexes = [$indexes];
        }
        $randomArray = [];
        foreach ($indexes as $index) {
            $randomArray[] = $array[$index];
        }

        return $randomArray;
    }

    /**
     * 08/2020 moved from UtilArray to UtilRandom
     *
     * @param array $array
     * @return mixed
     */
    public static function getRandomElement(array $array)
    {
        return $array[array_rand($array)];
    }

    /**
     * returns a random element and removes it from the array
     * 04/2022 created
     *
     * @param array &$array reference to the input array - gets modified by the function
     * @param bool $bReindexArray
     * @return mixed
     */
    public static function pullRandomElement(array &$array, bool $bReindexArray = false)
    {
        $idx = array_rand($array);
        $el = $array[$idx];
        if($bReindexArray) {
            array_splice($array, $idx, 1); // remove element, re-index array
        } else {
            unset($array[$idx]); // remove element, do not re-index array
        }

        return $el;
    }

}

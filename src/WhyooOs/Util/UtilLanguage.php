<?php


namespace WhyooOs\Util;

use Stichoza\GoogleTranslate\TranslateClient;
use WhyooOs\Util\UtilCurl;


/**
 * 08/2018
 */
class UtilLanguage
{

    /**
     * needs google translate api key (20$ / 1 Mio words)
     *
     * TODO: set current UtilCurl cache path
     * TODO: store in db
     *
     * @param $sourceLanguage
     * @param $targetLanguage
     * @param $phrase
     * @return mixed
     */
    private static function translateNonFree(string $sourceLanguage, string $targetLanguage, string $phrase, string $googleTranslateKey)
    {
        $url = "https://www.googleapis.com/language/translate/v2?key=' . $googleTranslateKey . '&source=$sourceLanguage&target=$targetLanguage&q=" . urlencode($phrase);
        $response = UtilCurl::curlGetJsonCached($url, 3600 * 24 * 365 * 10);

        if (!empty($response['data']['translations'][0]['translatedText'])) {
            return $response['data']['translations'][0]['translatedText'];
        }

        return false; // TRANSLATION FAILED
    }


    /**
     * free by reverse engineered token generation
     *
     * uses https://github.com/Stichoza/google-translate-php
     *
     * @param $sourceLanguage
     * @param $targetLanguage
     * @param $phrase
     * @return mixed
     */
    public static function translateFree(string $sourceLanguage, string $targetLanguage, string $phrase)
    {
        $tr = new TranslateClient($sourceLanguage, $targetLanguage);

        try {
            return $tr->translate($phrase);
        } catch(\Exception $exception) {
            // could be eg. that ip is blocked
//            var_dump($phrase);
//            var_dump($exception->getMessage());
//            UtilDebug::dd($exception->getMessage());
            return false;
        }
    }


    /**
     * @param string $text
     * @return int|null|string
     */
    public static function detectLanguage(string $text)
    {
        $ld = new \LanguageDetection\Language;

        $res = $ld->detect($text)->close();
        reset($res);
        $firstKey = key($res);

        return $firstKey;
    }

}



<?php


namespace WhyooOs\Util;


class UtilString
{

    /**
     * source http://stackoverflow.com/questions/1993721/how-to-convert-camelcase-to-camel-case
     * @param string $input
     * @return string
     */
    public static function to_snake_case($input, $glue='_')
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode($glue, $ret);

    }


    public static function toCamelCase($string, $capitalizeFirstCharacter = true, $separator = '_')
    {
        $str = str_replace(' ', '', ucwords(str_replace($separator, ' ', $string)));

        if (!$capitalizeFirstCharacter) {
            $str[0] = strtolower($str[0]);
        }

        return $str;
    }


    /**
     * indents multiline string
     * @param string $string
     * @param int $numSpaces
     * @return string indented string
     */
    public static function indent($string, $numSpaces)
    {
        $lines = explode("\n", $string);
        foreach ($lines as &$line) {
            $line = str_repeat(" ", $numSpaces) . $line;
        }

        return implode("\n", $lines);
    }


    /**
     * @param $str
     * @return string
     */
    public static function forceUtf8($str)
    {
        if (!self::isValidUtf8($str)) {
            return utf8_encode($str); // can produce garbage .. but should avoid MongoException "non-utf8 string"
        }
        return $str;
    }


    /**
     * source: http://php.net/manual/de/function.mb-check-encoding.php#95289
     *
     * @param $str
     * @return bool
     */
    public static function isValidUtf8($str)
    {
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $c = ord($str[$i]);
            if ($c > 128) {
                if (($c > 247)) return false;
                elseif ($c > 239) $bytes = 4;
                elseif ($c > 223) $bytes = 3;
                elseif ($c > 191) $bytes = 2;
                else return false;
                if (($i + $bytes) > $len) return false;
                while ($bytes > 1) {
                    $i++;
                    $b = ord($str[$i]);
                    if ($b < 128 || $b > 191) return false;
                    $bytes--;
                }
            }
        }
        return true;
    }

    /**
     * shortens $str if too long .. prepending "..."
     *
     * @param $str
     * @param $maxLength
     * @return string
     */
    public static function maxLength($str, $maxLength)
    {
        if( strlen($str) > $maxLength - 3) {
            return substr($str, 0, $maxLength-3) . '...';
        }

        return $str;
    }


}


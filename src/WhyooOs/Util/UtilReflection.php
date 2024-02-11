<?php

namespace WhyooOs\Util;


/**
 * 01/2024 created
 */
class UtilReflection
{
    /**
     * 01/2024 created
     *
     */
    public static function getClass(object $obj): string
    {
        return get_class($obj);
    }

    /**
     * 01/2024 created
     *
     */
    public static function getType(object|string $objOrClassname): string
    {
        if(is_string($objOrClassname)){
            $className = $objOrClassname;
        } else {
            $className = self::getClass($objOrClassname);
        }

        $classNameParts = explode('\\', $className);

        return end($classNameParts);
    }



    /**
     * former private helper for UtilDebug::dc()
     *
     * 05/2023 created
     * 02/2024 made it public
     * 02/2024 moved from UtilDebug to UtilReflection
     * @param mixed $object
     * @return string[]|string - string if not an object, string[] if an object
     */
    public static function getClassInheritance(mixed $object)
    {
        $classes = [];
        if (!is_object($object)) {
            return /*"not an object, but " . */ gettype($object);
        } else {
            $classes[] = get_class($object);
        }
        // ---- parent classes
        $class = $object;
        while (true) {
            $class = get_parent_class($class);
            if ($class) {
                $classes[] = $class;
            } else {
                break;
            }
        }

        return $classes;
    }

}
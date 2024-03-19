<?php

namespace WhyooOs\Util;


use ReflectionClass;

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

    /**
     * it is a utility which behaves like instanceof but for classnames (strings)
     *
     * 02/2024 created
     */
    public static function isClassOrSubclassOf(string $givenClass, string $wantedClass): bool
    {
        return $wantedClass === $givenClass || is_subclass_of($givenClass, $wantedClass);
    }


    /**
     * 04/2024 created
     *
     * @param string $givenClass
     * @param string[] $wantedClasses
     * @return bool
     */
    public static function isClassOrSubclassOfAny(string $givenClass, array $wantedClasses): bool
    {
        foreach($wantedClasses as $wantedClass){
            if(self::isClassOrSubclassOf($givenClass, $wantedClass)){
                return true;
            }
        }

        return false;
    }

    /**
     * It also searches parent classes for the property
     *
     * 03/2024 created
     *
     * @param object $object
     * @param string $propertyName
     * @return bool
     */
    public static function hasProperty(object $object, string $propertyName): bool
    {
        $reflection = new ReflectionClass($object);

        while ($reflection) {
            if ($reflection->hasProperty($propertyName)) {
                return true;
            }

            $reflection = $reflection->getParentClass();
        }

        return false;
    }


    /**
     * - Checks if a property of an object is set
     * - it also checks parent classes
     * - throws an exception if the property does not exist
     *
     * @param object $object The object to check
     * @param string $propertyName The name of the property to check
     * @return bool True if the property is set, false otherwise
     */
    public static function isPropertyInitialized(object $object, string $propertyName): bool
    {

        $reflection = new ReflectionClass($object);

        while ($reflection) {
            if ($reflection->hasProperty($propertyName)) {
                $property = $reflection->getProperty($propertyName);
                $property->setAccessible(true); // Make private properties accessible

                return $property->isInitialized($object);
            }

            $reflection = $reflection->getParentClass();
        }

        throw new \InvalidArgumentException("Property '$propertyName' does not exist in the object.");
    }


}
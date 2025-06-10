<?php

namespace Netmex\RequestBundle\Util;

use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;

class ReflectionCache
{
    private static array $classCache = [];
    private static array $defaultPropertyCache = [];
    private static array $propertyTypeCache = [];

    public static function getReflectionClass(string|object $classOrObject): ReflectionClass
    {
        $class = \is_object($classOrObject) ? \get_class($classOrObject) : $classOrObject;

        return self::$classCache[$class] ??= new ReflectionClass($class);
    }

    public static function getDefaultProperties(object $object): array
    {
        $class = \get_class($object);

        return self::$defaultPropertyCache[$class]
            ??= self::getReflectionClass($class)->getDefaultProperties();
    }

    public static function getPropertyType(object $object, string $property): ?string
    {
        $class = \get_class($object);
        $key = "$class::$property";

        if (!array_key_exists($key, self::$propertyTypeCache)) {
            $refClass = self::getReflectionClass($class);
            if (!$refClass->hasProperty($property)) {
                return null;
            }

            $type = $refClass->getProperty($property)->getType();
            self::$propertyTypeCache[$key] = $type instanceof ReflectionNamedType
                ? $type->getName()
                : null;
        }

        return self::$propertyTypeCache[$key];
    }

    public static function getDeclaredPropertyNames(object $object): array
    {
        $refClass = self::getReflectionClass($object);
        $className = $refClass->getName();

        return array_map(
            fn(ReflectionProperty $prop) => $prop->getName(),
            array_filter(
                $refClass->getProperties(),
                fn(ReflectionProperty $prop) => $prop->getDeclaringClass()->getName() === $className
            )
        );
    }
}

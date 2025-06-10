<?php

namespace Netmex\RequestBundle\Paginator;

use Netmex\RequestBundle\Attribute\Paginator;

class PaginatorMetadataResolver
{
    private const CACHE_KEY = "";


    public function getMetaData(string $class)
    {
        $refClass = new \ReflectionClass($class);
        $attributes = $refClass->getAttributes(Paginator::class);
        if (!empty($attributes)) {
            return $attributes[0]->newInstance(); // returns the actual Paginator instance with page, limit
        }
        return null;
    }
}
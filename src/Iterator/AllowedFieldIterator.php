<?php

namespace Netmex\RequestBundle\Iterator;

use Netmex\RequestBundle\Util\ReflectionCache;

class AllowedFieldIterator implements \IteratorAggregate
{
    private const NOT_ALLOWED_FIELDS = [
        'content',
        'method',
        'headers',
        'contentType'
    ];

    private array $allowedProperties = [];

    public function __construct(object $dtoInstance)
    {
        $this->allowedProperties = ReflectionCache::getDeclaredPropertyNames($dtoInstance);
        $this->removeNotAllowedFields();
    }

    private function removeNotAllowedFields(): void
    {
        $this->allowedProperties = array_diff($this->allowedProperties, self::NOT_ALLOWED_FIELDS);
    }

    public function getAllowedProperties(): array
    {
        return $this->allowedProperties;
    }


    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->allowedProperties);
    }
}

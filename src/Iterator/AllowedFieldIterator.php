<?php

namespace Netmex\RequestBundle\Iterator;

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
        $reflection = new \ReflectionClass($dtoInstance);

        foreach ($reflection->getProperties() as $property) {
            if ($property->getDeclaringClass()->getName() === get_class($dtoInstance)) {
                $this->allowedProperties[] = $property->getName();
            }
        }

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

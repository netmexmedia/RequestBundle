<?php

namespace Netmex\RequestBundle\Iterator;

use Iterator;
use Netmex\RequestBundle\Parameter\ParameterBag;

class ValidatedFieldIterator implements Iterator
{
    private array $keys;
    private int $position = 0;

    public function __construct(
        private ParameterBag $bag
    ) {
        $this->keys = array_keys($bag->all());
    }

    public function current(): mixed
    {
        $field = $this->bag->all()[$this->key()];
        return $field->validate();
    }

    public function key(): mixed
    {
        return $this->keys[$this->position];
    }

    public function next(): void
    {
        $this->position++;
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        return isset($this->keys[$this->position]);
    }
}

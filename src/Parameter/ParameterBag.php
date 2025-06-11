<?php

namespace Netmex\RequestBundle\Parameter;

use Netmex\RequestBundle\Factory\ParameterFactory;
use Netmex\RequestBundle\Iterator\AllowedFieldIterator;
use Netmex\RequestBundle\Request\AbstractRequest;
use Netmex\RequestBundle\Util\ReflectionCache;

class ParameterBag implements \IteratorAggregate, \Countable
{
    protected array $data = [];
    private ParameterFactory $parameterFactory;
    private AbstractRequest $abstractRequest;

    public function __construct(ParameterFactory $parameterFactory, AbstractRequest $abstractRequest, array $data = [])
    {
        $this->parameterFactory = $parameterFactory;
        $this->abstractRequest = $abstractRequest;

        $allowedFields = new AllowedFieldIterator($abstractRequest);
        $defaultProperties = ReflectionCache::getDefaultProperties($abstractRequest);

        foreach ($allowedFields as $field) {
            $value = null;

            if (array_key_exists($field, $data)) {
                $value = $data[$field];
            } elseif (array_key_exists($field, $defaultProperties)) {
                $value = $defaultProperties[$field];
            }

            if ($value === null && ReflectionCache::hasNullableAttribute($abstractRequest, $field)) {
                continue;
            }

            $this->set($field, $value);
        }
    }

    public function removeNullValues(): void
    {
        $this->data = array_filter($this->data, function ($value) {
            return $value->value() !== null;
        });

        $this->data = array_values($this->data);
    }

    public function all(?string $key = null): array|string|Parameter
    {
        if (null !== $key) {
            return $this->data[$key];
        }

        return $this->data;
    }

    public function keys(): array
    {
        return array_keys($this->all());
    }

    public function replace(array $data = []): void
    {
        $this->data = [];
        $this->add($data);
    }

    public function add(array $data): void
    {
        foreach ($data as $key => $values) {
            $this->set($key, $values);
        }
    }

    public function get(string $key, $default = null): array|string|Parameter
    {
        if (!isset($this->data[$key])) {
            return $default;
        }

        $value = $this->data[$key];

        if ($value instanceof ParameterBag) {
            return $value->toArray();
        }

        if ($value instanceof Parameter) {
            return $value->value();
        }

        return $value;
    }

    public function toArray(): array
    {
        $result = [];
        foreach ($this->data as $key => $value) {
            if ($value instanceof ParameterBag) {
                $result[$key] = $value->toArray();
            } elseif ($value instanceof Parameter) {
                $result[$key] = $value->value();
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    public function set(string $key, $values, bool $replace = true): void
    {
        if (is_array($values)) {
            if ($this->isAssoc($values)) {
                $nestedBag = new ParameterBag($this->parameterFactory, $this->abstractRequest, $values);
                $this->data[$key] = $nestedBag;
                return;
            } else {
                if ($replace || !isset($this->data[$key])) {
                    $this->data[$key] = $values;
                } else {
                    $this->data[$key] = array_merge($this->data[$key], $values);
                }
                return;
            }
        }

        $parameter = $this->parameterFactory->create($key, $values, $this->abstractRequest);
        $this->data[$key] = $parameter;
    }

    private function isAssoc(array $arr): bool
    {
        if ([] === $arr) {
            return false;
        }

        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    public function has(string $key): bool
    {
        return \array_key_exists($key, $this->all());
    }

    public function contains(string $key, string $value): bool
    {
        return \in_array($value, $this->all($key), true);
    }

    public function remove(string $key): void
    {
        unset($this->data[$key]);
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->data);
    }

    public function count(): int
    {
        return \count($this->data);
    }
}

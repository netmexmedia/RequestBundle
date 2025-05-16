<?php

namespace Netmex\RequestBundle\Parameter;

use Netmex\RequestBundle\Factory\ParameterFactory;
use Netmex\RequestBundle\Iterator\AllowedFieldIterator;
use Netmex\RequestBundle\Request\AbstractRequest;

class ParameterBag implements \IteratorAggregate, \Countable
{
    protected array $data = [];
    private ParameterFactory $parameterFactory;
    private AbstractRequest $abstractRequest;

    public function __construct(array $data = [], ParameterFactory $parameterFactory, AbstractRequest $abstractRequest)
    {
        $this->parameterFactory = $parameterFactory;
        $this->abstractRequest = $abstractRequest;

        $allowedFields = new AllowedFieldIterator($abstractRequest);

        foreach ($allowedFields as $field) {
            $value = null;

            if (array_key_exists($field, $data)) {
                $value = $data[$field];
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

    public function get(string $key, ?string $default = null)
    {
        $data = $this->all($key);

        if (!$data->value()) {
            return $this->parameterFactory->create($key, $default, $this->abstractRequest);
        }

        return $data;
    }

    public function set(string $key, string|array|null $values, bool $replace = true): void
    {
        $parameter = $this->parameterFactory->create($key, $values, $this->abstractRequest);

        if (\is_array($values)) {
            $values = array_values($values);

            if ($replace || !isset($this->data[$key])) {
                $this->data[$key] = $parameter;
            } else {
                $this->data[$key] = array_merge($this->data[$key], $parameter);
            }

            return;
        }

        $this->data[$key] = $parameter;
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

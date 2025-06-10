<?php

namespace Netmex\RequestBundle\Request;

use Netmex\RequestBundle\Factory\ParameterFactory;
use Netmex\RequestBundle\Iterator\ValidatedFieldIterator;
use Netmex\RequestBundle\Parameter\ParameterBag;

abstract class AbstractRequest
{
    public ParameterBag $content;

    private ParameterFactory $parameterFactory;

    private array $orderBy = [];

    private ?PaginatorRequest $paginator;

    public function __construct(
        array $content,
        ParameterFactory $parameterFactory,
    ) {
        $this->parameterFactory = $parameterFactory;
        $this->content = new ParameterBag($this->parameterFactory, $this, $content);
        $this->paginator = null;
    }

    public function get(string $key, ?string $default = null): mixed
    {
        return $this->content->get($key, $default)->validate();
    }

    public function content(): array
    {
        $iterator = new ValidatedFieldIterator($this->content);
        $results = [];

        foreach ($iterator as $key => $value) {
            $results[$key] = $value;
        }

        return $results;
    }

    public function setOrderBy(array $orderBy): void
    {
        $this->orderBy = $orderBy;
    }

    public function orderBy(): array
    {
        return $this->orderBy;
    }

    public function setPaginator(PaginatorRequest $paginator): void
    {
        $this->paginator = $paginator;
    }

    public function paginator(): array
    {
        return $this->paginator?->content() ?? [];
    }
}

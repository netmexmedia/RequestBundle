<?php

namespace Netmex\RequestBundle\Paginator;

class PaginatorRequest
{
    public int $limit;
    public int $page;
    public array $orderBy = [];

    /** @var object|null */
    public ?object $criteria = null;

    public function __construct(int $limit = 25, int $page = 1, array $orderBy = [], ?object $criteria = null)
    {
        $this->limit = $limit;
        $this->page = $page;
        $this->orderBy = $orderBy;
        $this->criteria = $criteria;
    }

    public function getOffset(): int
    {
        return ($this->page - 1) * $this->limit;
    }

    public function toArray(): array
    {
        return [
            'criteria' => $this->criteria,
            'limit' => $this->limit,
            'page' => $this->page,
            'orderBy' => $this->orderBy,
        ];
    }
}
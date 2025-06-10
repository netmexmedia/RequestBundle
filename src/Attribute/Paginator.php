<?php

namespace Netmex\RequestBundle\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Paginator
{
    public int $page;
    public int $limit;
    public ?string $orderBy;

    public function __construct(int $page = 1, int $limit = 25, ?string $orderBy = null)
    {
        $this->page = $page;
        $this->limit = $limit;
        $this->orderBy = $orderBy;
    }
}
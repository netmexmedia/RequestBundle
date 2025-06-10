<?php

namespace Netmex\RequestBundle\Attribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class OrderBy
{
    public function __construct(public ?string $name = null) {}
}

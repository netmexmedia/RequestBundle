<?php

namespace Netmex\RequestBundle\Factory;

use Netmex\RequestBundle\Content\Content;
use Netmex\RequestBundle\Request\AbstractRequest;

class ContentFactory
{

    public function create(mixed $content, AbstractRequest $abstractRequest): Content
    {
        return new Content($content, $abstractRequest);
    }
}

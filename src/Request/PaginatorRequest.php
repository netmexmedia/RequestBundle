<?php

namespace Netmex\RequestBundle\Request;

use Symfony\Component\Validator\Constraints as Assert;

class PaginatorRequest extends AbstractRequest
{
    #[Assert\GreaterThanOrEqual(1)]
    public int $page = 1;

    #[Assert\GreaterThanOrEqual(1)]
//    #[Assert\Type("int")]
    public int $limit = 25;

    #[Assert\Type("string")]
    public ?string $orderBy = null;
}

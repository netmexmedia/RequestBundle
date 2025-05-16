<?php

namespace Netmex\RequestBundle\Factory;

use Netmex\RequestBundle\Parameter\Parameter;
use Netmex\RequestBundle\Request\AbstractRequest;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ParameterFactory
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function create(string $key, mixed $value, AbstractRequest $abstractRequest): Parameter
    {
        return new Parameter($key, $value, $this->validator, $abstractRequest);
    }
}

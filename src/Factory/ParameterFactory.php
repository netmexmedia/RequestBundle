<?php

namespace Netmex\RequestBundle\Factory;

use Netmex\RequestBundle\Caster\TypeCaster;
use Netmex\RequestBundle\Parameter\Parameter;
use Netmex\RequestBundle\Request\AbstractRequest;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ParameterFactory
{
    private ValidatorInterface $validator;

    private TypeCaster $caster;

    public function __construct(ValidatorInterface $validator, TypeCaster $caster)
    {
        $this->validator = $validator;
        $this->caster = $caster;
    }

    public function create(string $key, mixed $value, AbstractRequest $abstractRequest): Parameter
    {
        $value = $this->caster->cast($abstractRequest, $key, $value);

        return new Parameter($key, $value, $this->validator, $abstractRequest);
    }
}

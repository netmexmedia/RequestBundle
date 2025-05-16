<?php

namespace Netmex\RequestBundle\Content;

use Netmex\RequestBundle\Parameter\ParameterBag;
use Netmex\RequestBundle\Request\AbstractRequest;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class Content
{
    private AbstractRequest $abstractRequest;

    private ParameterBag $content;

    public function __construct(ParameterBag $content, AbstractRequest $abstractRequest)
    {
        $this->content = $content;
        $this->abstractRequest = $abstractRequest;
    }

    public function validate()
    {
        $violations = new ConstraintViolationList();

        /** @var Parameter $parameter */
        foreach ($this->content as $parameter) {
            foreach ($parameter->validate(true) as $violation) {
                $violations->add($violation);
            }
        }

        if (count($violations) > 0) {
            throw new ValidationFailedException($this->abstractRequest, $violations);
        }

        return $this->value();
    }

    public function value(): array
    {
        $data = [];

        foreach ($this->content as $parameter) {
            $data[$parameter->key()] = $parameter->value();
        }

        return $data;
    }
}

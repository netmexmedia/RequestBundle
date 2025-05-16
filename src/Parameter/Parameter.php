<?php

namespace Netmex\RequestBundle\Parameter;

use Netmex\RequestBundle\Request\AbstractRequest;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Parameter
{
    private ValidatorInterface $validator;

    private AbstractRequest $abstractRequest;

    private string $key;

    private mixed $value;

    public function __construct(string $key, mixed $value, ValidatorInterface $validator, AbstractRequest $abstractRequest)
    {
        $this->key = $key;
        $this->value = $value;
        $this->validator = $validator;
        $this->abstractRequest = $abstractRequest;
    }

    public function validate(bool $suppress = false): mixed
    {
        $violations = $this->validator->validatePropertyValue($this->abstractRequest, $this->key, $this->value);

        if ($suppress) {
            return $violations;
        }

        if (count($violations) > 0) {
            throw new ValidationFailedException($this->abstractRequest, $violations);
        }

        return $this->value;
    }

    public function value(): mixed
    {
        return $this->value;
    }

    public function key(): string
    {
        return $this->key;
    }
}

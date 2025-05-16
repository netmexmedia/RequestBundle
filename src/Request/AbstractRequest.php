<?php

namespace Netmex\RequestBundle\Request;

use Netmex\RequestBundle\Content\Content;
use Netmex\RequestBundle\Factory\ContentFactory;
use Netmex\RequestBundle\Factory\ParameterFactory;
use Netmex\RequestBundle\Iterator\AllowedFieldIterator;
use Netmex\RequestBundle\Parameter\ParameterBag;

abstract class AbstractRequest
{
    public ParameterBag $content;

    public $method;

    public $headers;

    public $contentType;

    private ParameterFactory $parameterFactory;

    private ContentFactory $contentFactory;

    public function __construct(
        array $content,
        string $method,
        $headers,
        $contentType,
        ParameterFactory $parameterFactory,
        ContentFactory $contentFactory
    ) {
        $this->parameterFactory = $parameterFactory;
        $this->contentFactory = $contentFactory;
        $this->content = new ParameterBag($content, $this->parameterFactory, $this);
        $this->method = $method;
        $this->headers = $headers;
        $this->contentType = $contentType;
    }

    public function getAllowedFields(): \IteratorAggregate
    {
        return new AllowedFieldIterator($this);
    }

    public function get(string $key, ?string $default = null)
    {
        /** @var AllowedFieldIterator $allowedFields */
        $allowedFields = $this->getAllowedFields();

        if (!in_array($key, $allowedFields->getAllowedProperties(), true)) {
            throw new \InvalidArgumentException("Field '{$key}' is not allowed.");
        }

        return $this->content->get($key, $default);
    }

    public function getContent(bool $strict = true): Content
    {
        if ($strict) {
            $this->content->removeNullValues();
        }

        return $this->contentFactory->create($this->content, $this);
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getHeaders(): mixed
    {
        return $this->headers;
    }

    public function getContentType(): mixed
    {
        return $this->contentType;
    }

    public function isXmlHttpRequest() {}

    public function isJson() {}

    public function isXml() {}
}

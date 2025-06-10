<?php

namespace Netmex\RequestBundle\ArgumentResolver;

use Netmex\RequestBundle\Attribute\Paginator;
use Netmex\RequestBundle\Factory\ParameterFactory;
use Netmex\RequestBundle\Request\AbstractRequest;
use Netmex\RequestBundle\Request\PaginatorRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class RequestArgumentResolver implements ValueResolverInterface
{
    private ParameterFactory $parameterFactory;

    public function __construct(ParameterFactory $parameterFactory)
    {
        $this->parameterFactory = $parameterFactory;
    }

    public function supports(string $type): bool
    {
        return is_subclass_of($type, AbstractRequest::class);
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {

        $type = $argument->getType();

        if (!$this->supports($type)) {
            return [];
        }

        $data = $this->extractRequestData($request);

        /** @var AbstractRequest $type */
        $abstractRequest = new $type(
            $data,
            $this->parameterFactory
        );

        $paginator = $this->resolvePaginatorIfNeeded($abstractRequest, $request);
        if ($paginator) {
            // You could inject paginator into DTO or yield a wrapper
            $abstractRequest->setPaginator($paginator);
        }

        yield $abstractRequest;
    }

    private function resolvePaginatorIfNeeded(AbstractRequest $abstractRequest, Request $request): ?PaginatorRequest
    {
        $refClass = new \ReflectionClass($abstractRequest);
        $attributes = $refClass->getAttributes(Paginator::class);

        if (empty($attributes)) {
            return null;
        }

        return new PaginatorRequest(
            $this->extractRequestData($request),
            $this->parameterFactory
        );
    }


    private function extractRequestData(Request $request): array
    {
        if ($request->isMethod('GET')) {
            return $request->query->all();
        }

        if ($this->isJsonRequest($request)) {
            return $this->parseJsonRequest($request);
        }

        return $request->request->all();
    }

    private function isJsonRequest(Request $request): bool
    {
        return $request->getContentTypeFormat() === 'json';
    }

    private function parseJsonRequest(Request $request): array
    {
        $json = json_decode($request->getContent(), true);
        return is_array($json) ? $json : [];
    }

}

<?php

namespace Netmex\RequestBundle\ArgumentResolver;

use Netmex\RequestBundle\Attribute\OrderBy;
use Netmex\RequestBundle\Attribute\Paginator;
use Netmex\RequestBundle\Factory\ParameterFactory;
use Netmex\RequestBundle\Request\AbstractRequest;
use Netmex\RequestBundle\Request\PaginatorRequest;
use Netmex\RequestBundle\Util\ReflectionCache;
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
        $orderByField = $data['orderBy'] ?? null;
        $orderDirection = $data['orderDirection'] ?? null;

        unset($data['orderBy'], $data['orderDirection']);

        /** @var AbstractRequest $type */
        $abstractRequest = new $type(
            $data,
            $this->parameterFactory
        );

        $orderBy = $this->resolveOrderBy($abstractRequest, $orderByField, $orderDirection);
        if ($orderBy) {
            $abstractRequest->setOrderBy($orderBy);
        }

        $paginator = $this->resolvePaginatorIfNeeded($abstractRequest, $request, $data);
        if ($paginator) {
            $abstractRequest->setPaginator($paginator);
        }

        yield $abstractRequest;
    }

    private function resolveOrderBy(AbstractRequest $requestDto, ?string $field, ?string $direction): array
    {
        if (!$field || !in_array(strtoupper($direction), ['ASC', 'DESC'], true)) {
            return [];
        }

        $sortable = ReflectionCache::getOrderableFields($requestDto);

        if (!isset($sortable[$field])) {
            return [];
        }

        return [$sortable[$field] => strtoupper($direction)];
    }

    private function resolvePaginatorIfNeeded(AbstractRequest $abstractRequest, Request $request, array $data): ?PaginatorRequest
    {
        if (!ReflectionCache::hasPaginatorAttribute($abstractRequest)) {
            return null;
        }

        return new PaginatorRequest(
            $data,
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

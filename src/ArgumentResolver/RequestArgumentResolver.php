<?php

namespace Netmex\RequestBundle\ArgumentResolver;

use Netmex\RequestBundle\Factory\ContentFactory;
use Netmex\RequestBundle\Factory\ParameterFactory;
use Netmex\RequestBundle\Request\AbstractRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class RequestArgumentResolver implements ValueResolverInterface
{
    private array $allowedFormats = ['json', 'xml', 'yaml'];

    private ParameterFactory $parameterFactory;

    private ContentFactory $contentFactory;

    public function __construct(ParameterFactory $parameterFactory, ContentFactory $contentFactory)
    {
        $this->parameterFactory = $parameterFactory;
        $this->contentFactory = $contentFactory;
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

        $this->contentTypeValidator($request);

        /** @var AbstractRequest $type */
        $abstractRequest = new $type(
            $request->toArray(),
            $request->getMethod(),
            $request->headers,
            $request->getContentTypeFormat(),
            $this->parameterFactory,
            $this->contentFactory
        );

        yield $abstractRequest;
    }

    public function contentTypeValidator(Request $request): void
    {
        $format = $request->getContentTypeFormat();
        if (!in_array($format, $this->allowedFormats, true)) {
            throw new BadRequestHttpException('Unsupported content type: ' . $format);
        }
    }
}

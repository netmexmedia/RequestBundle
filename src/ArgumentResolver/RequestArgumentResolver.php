<?php

namespace Netmex\RequestBundle\ArgumentResolver;

use Netmex\RequestBundle\Factory\ContentFactory;
use Netmex\RequestBundle\Factory\ParameterFactory;
use Netmex\RequestBundle\Request\AbstractRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Constraints\NotBlank;

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

        $data = $this->extractRequestData($request);

        $this->contentTypeValidator($request);

        $this->validateNotBlankFields($type, $data);

        /** @var AbstractRequest $type */
        $abstractRequest = new $type(
            $data,
            $request->getMethod(),
            $request->headers,
            $request->getContentTypeFormat(),
            $this->parameterFactory,
            $this->contentFactory
        );

        yield $abstractRequest;
    }

    private function extractRequestData(Request $request): array
    {
        return $request->isMethod('GET') ? $request->query->all() : $request->request->all();
    }

    public function contentTypeValidator(Request $request): void
    {
        $format = $request->getContentTypeFormat();
        if (!in_array($format, $this->allowedFormats, true)) {
            throw new BadRequestHttpException('Unsupported content type: ' . $format);
        }
    }

    private function validateNotBlankFields(string $dtoClass, array $data): void
    {
        $refClass = new \ReflectionClass($dtoClass);
        foreach ($refClass->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            $attributes = $property->getAttributes(NotBlank::class);
            if (count($attributes) === 0) {
                continue;
            }

            $name = $property->getName();

            if (!isset($data[$name]) || $this->isBlank($data[$name])) {
                throw new BadRequestHttpException("Field '{$name}' must not be blank.");
            }
        }
    }

    private function isBlank(mixed $value): bool
    {
        return $value === null || $value === '' || (is_array($value) && empty($value));
    }
}

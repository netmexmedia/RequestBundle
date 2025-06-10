<?php

namespace Netmex\RequestBundle\Caster;

use Netmex\RequestBundle\Util\ReflectionCache;

class TypeCaster
{
    public function cast(object $dto, string $propertyName, mixed $value): mixed
    {
        $typeName = ReflectionCache::getPropertyType($dto, $propertyName);

        if ($value === null) {
            return null;
        }

        return match ($typeName) {
            'int' => (int) $value,
            'float' => (float) $value,
            'bool' => $this->castBool($value),
            'string' => (string) $value,
            'array' => $this->castArray($value),
            default => $value,
        };
    }

    private function castBool(mixed $value): bool
    {
        if (is_string($value)) {
            return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
        }

        return (bool) $value;
    }

    private function castArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            $json = json_decode($value, true);
            return is_array($json) ? $json : [$value];
        }

        return [$value];
    }
}

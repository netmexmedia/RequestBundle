# Netmex RequestBundle

## Installation

```bash
composer require netmex/request-bundle
```

## Usage

To use the ```RequestBundle```, create a DTO class that extends ```Netmex\RequestBundle\Request\AbstractRequest```.
You can use Symfony’s ```#[Assert\...]``` attributes to define validation rules per property.

##### Example DTO
```php
<?php

namespace App\Request;

use Netmex\RequestBundle\Request\AbstractRequest;
use Symfony\Component\Validator\Constraints as Assert;

class CustomRequest extends AbstractRequest
{
    #[Assert\NotBlank()]
    public string $exampleField;
}

```

### Injecting the DTO into a Controller

```php
<?php

namespace App\Controller;

use App\DTO\CustomRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExampleController
{
    #[Route('/example', name: 'app_example')]
    public function index(CustomRequest $request): Response
    {
        // Validated and sanitized content
        $results = $request->getContent()->validate();

        return new Response($results);
    }
}
```

### Accessing Fields

##### All fields
Use ```getContent(bool $suppress = true)``` to retrieve all fields.

```php
// Raw values (filtered if $suppress = true), default is true
$request->getContent()->value();
```

```php
// Validated values (throws on error)
$request->getContent()->validate();

```

##### Single field
You can also fetch a single field using the ```get(string $key, ?string $default = null): Parameter```.

```php
// Raw value (with optional fallback)
$request->get('fieldName')->value();
```

```php
// Validated value (throws on constraint violations)
$request->get('fieldName')->validate();
```

## Recommended Directory Layout
```text
src/
├── Request/
│   └── YourDTO.php
└── Controller/
    └── YourController.php
```

## Exception Handling
```php
try {
    $data = $request->getContent()->validate();
} catch (ValidationFailedException $e) {
    return new JsonResponse(['errors' => (string) $e->getViolations()], 400);
}
```

## More about Constraints
See [Symfony Validation Constraints](https://symfony.com/doc/current/validation.html#constraints) for available options.

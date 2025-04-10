# JSON Test Utilities for PHPUnit

[![Latest Version on Packagist](https://img.shields.io/packagist/v/fallegahq/phpunit-json-test-utils.svg)](https://packagist.org/packages/fallegahq/phpunit-json-test-utils)
[![Tests](https://github.com/fallegahq/phpunit-json-test-utils/actions/workflows/run-tests.yml/badge.svg)](https://github.com/fallegahq/phpunit-json-test-utils/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/fallegahq/phpunit-json-test-utils.svg)](https://packagist.org/packages/fallegahq/phpunit-json-test-utils)

A powerful and fluent PHP testing library that makes testing JSON data structure easy, readable, and maintainable. Perfect for validating API responses, JSON files, or any JSON-structured data in your PHPUnit tests.

## Features

- Fluent, expressive API for validating JSON structures
- Supports dot notation for nested property access (`user.address.zipcode`)
- Type checking for values (string, integer, array, etc.)
- Comprehensive validation rules (regex, email, URL, date, etc.)
- Supports custom validation callbacks
- Schema validation for complex structures
- Detailed error messages for failed assertions

## Installation

You can install the package via composer:

```bash
composer require --dev fallegahq/phpunit-json-test-utils
```

## Usage

### Basic Usage

```php
use FallegaHQ\PhpunitJsonTestUtils\JsonAssertions;

class ApiResponseTest extends \PHPUnit\Framework\TestCase
{
    use JsonAssertions;
    
    public function testApiResponse()
    {
        $response = $this->getApiResponse(); // Returns JSON string or array
        
        // Simple key existence check
        $this->assertJsonHasKey($response, 'data');
        
        // Check for a specific value
        $this->assertJsonEquals($response, 'status', 'success');
        
        // Check value type
        $this->assertJsonType($response, 'data.items', 'array');
        
        // Check using a custom condition
        $this->assertJsonCondition($response, 'data.count', function($value) {
            return $value > 0 && $value < 100;
        });
    }
}
```

### Fluent API

```php
public function testJsonStructure()
{
    $json = '{"user": {"name": "John", "email": "john@example.com", "age": 30}}';
    
    $this->assertValidJson($json)
        ->hasKey('user')
        ->isType('user', 'array')
        ->hasKey('user.name')
        ->equals('user.name', 'John')
        ->isEmail('user.email')
        ->isType('user.age', 'integer')
        ->assert();
}
```

### Schema Validation

```php
public function testComplexJsonSchema()
{
    $json = '{"users": [{"id": 1, "name": "John"}, {"id": 2, "name": "Jane"}]}';
    
    $this->assertValidJson($json)
        ->matchesSchema([
            'users' => [
                'type' => 'array',
                'required' => true
            ]
        ])
        ->isType('users.0.id', 'integer')
        ->isType('users.0.name', 'string')
        ->hasLength('users', null, 1) // At least 1 user
        ->assert();
}
```

## Available Assertions

### Trait Methods

- `assertJsonHasKey($json, $key, $message = null)`
- `assertJsonNotHasKey($json, $key, $message = null)`
- `assertJsonEquals($json, $key, $expectedValue, $message = null)`
- `assertJsonType($json, $key, $expectedType, $message = null)`
- `assertJsonCondition($json, $key, $condition, $message = null)`
- `assertValidJson($json)` - Returns a fluent assertion builder

### Fluent Assertion Methods

- `hasKey($key)`
- `hasKeys(array $keys)`
- `notHasKey($key)`
- `hasAnyKey(...$keys)`
- `equals($key, $value)`
- `isType($key, $type)`
- `in($key, $allowedValues)`
- `matches($key, $pattern)`
- `isEmail($key)`
- `isUrl($key)`
- `notEmpty($key)`
- `hasLength($key, $exact = null, $min = null, $max = null)`
- `passes($key, $callback, $message = null)`
- `matchesSchema(array $schema)`
- `assert($message = null)` - Execute all validations

### JsonValidator Methods

For advanced use cases, you can use the `JsonValidator` class directly:

```php
use FallegaHQ\PhpunitJsonTestUtils\JsonValidator;

$validator = new JsonValidator($json);
$validator->has('data')
    ->whereType('data', 'array')
    ->whereNotEmpty('data')
    ->whereEach('data.items', function($item) {
        return isset($item['id']) ? true : 'Item must have an ID';
    });

if ($validator->fails()) {
    var_dump($validator->errors());
}
```

## Supported Types

The library supports the following types for validation:

- `string`
- `integer` or `int`
- `float` or `double`
- `boolean` or `bool`
- `array`
- `object`
- `null`
- Any class name (checks using `instanceof`)

## Advanced Features

### Date Validation

```php
$validator->whereDate('created_at', 'Y-m-d H:i:s');
```

### IP Address Validation

```php
$validator->whereIp('server.address');
$validator->whereIp('server.address', FILTER_FLAG_IPV4); // IPv4 only
```

### String Content Validation

```php
$validator->whereContains('description', 'important');
```

### Array Item Validation

```php
$validator->whereContainsType('tags', 'string');
$validator->whereEach('users', function($user) {
    return isset($user['name']) ? true : 'User must have a name';
});
```

## Requirements

- PHP 8.2+
- PHPUnit 11.0+

## License

The MIT License (MIT). Please see [License File](LICENSE.MD) for more information.

## Credits

- [Fallega HQ](https://github.com/fallegahq)
- [All Contributors](/contributors)

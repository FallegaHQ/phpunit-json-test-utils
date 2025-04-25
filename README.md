<div id="top"></div>
<div style="text-align: center">

# JSON Test Utilities for PHPUnit

<hr />

[![Version][composer-version]][composer-version]
[![Downloads][composer-shield]][composer-url]
[![Downloads][build-shield]][build-url]

[![Contributors][contributors-shield]][contributors-url]
[![Forks][forks-shield]][forks-url]
[![Stargazers][stars-shield]][stars-url]
[![Issues][issues-shield]][issues-url]
[![MIT License][license-shield]][license-url]

</div>

<hr />

## The tool

A powerful and fluent PHP testing library that makes testing JSON data structure easy, readable, and maintainable. Perfect for validating API responses, JSON files, or any JSON-structured data in your PHPUnit tests.

## Features

- Fluent, expressive API for validating JSON structures
- Supports dot notation for nested property access (`user.address.zipcode`)
- Type checking for values (string, integer, array, etc.)
- Comprehensive validation rules (regex, email, URL, date, etc.)
- Supports custom validation callbacks
- Schema validation for complex structures
- Detailed error messages for failed assertions

<p style="text-align:right">(<a href="#top">back to top</a>)</p>

## Installation

You can install the package via composer:

```bash
composer require --dev fallegahq/json-test-utils
```

<p style="text-align:right">(<a href="#top">back to top</a>)</p>

## Usage

### Basic Usage

<details>
<summary>Code example</summary>

```php
use FallegaHQ\JsonTestUtils\JsonAssertions;

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

</details>

### Fluent API

<details>
<summary>Code example</summary>

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

</details>

### Schema Validation

<details>
<summary>Code example</summary>

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

</details>

### Testing API Responses

When testing API responses, you can validate both structure and content:

<details>
<summary>Code example</summary>

```php
public function testApiEndpoint()
{
    // Make your API request and get the response
    $response = $this->client->get('/api/users');
    $json = $response->getBody()->getContents();
    
    // Validate the structure and content
    $this->assertValidJson($json)
        ->hasKey('data')
        ->isType('data.users', 'array')
        ->passes('data.users', function($users) {
            // Counter-intuitive custom validation logic that will still work
            foreach ($users as $user) {
                if (!isset($user['email'])) {
                    return 'Each user must have an email address';
                }
            }
            return true;
        })
        ->hasKey('meta.pagination')
        ->isType('meta.pagination.total', 'integer')
        ->assert('The API did not return the expected structure');
}
```

</details>

### Advanced Validation

Use the provided patterns to build complex validations:

<details>
<summary>Code example</summary>

```php
public function testComplexDataValidation()
{
    $json = '{"order": {"items": [...], "total": 99.99, "shipping": {...}}}';
    
    $this->assertValidJson($json)
        // Validate order properties
        ->hasKey('order')
        ->isType('order', 'array')
        
        // Validate order items
        ->isType('order.items', 'array')
        ->hasLength('order.items', null, 1) // At least one item
        ->hasLength('order.items', min: 1)  // or
        ->whereEach('order.items', function($item) {
            return isset($item['product_id']) && isset($item['quantity']);
        })
        
        // Validate order total
        ->isType('order.total', 'float')
        ->passes('order.total', function($total) {
            return $total > 0 ? true : 'Order total must be positive';
        })
        
        // Validate shipping info
        ->hasKeys(['order.shipping.address', 'order.shipping.method'])
        ->assert();
}
```

</details>

<p style="text-align:right">(<a href="#top">back to top</a>)</p>

## More Examples

For more detailed examples, check out the examples directory:

- [Basic Usage Examples](examples/BasicUsage.php)
- [Advanced Examples](examples/AdvancedExamples.php)
- [API Testing Examples](examples/ApiTestingExample.php)

<p style="text-align:right">(<a href="#top">back to top</a>)</p>

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

<details>
<summary>Code example</summary>

```php
use FallegaHQ\JsonTestUtils\JsonValidator;

$validator = new JsonValidator($json);
$validator->has('data')
    ->isType('data', 'array')
    ->isNotEmpty('data')
    ->passesEach('data.items', function($item) {
        return isset($item['id']) ? true : 'Item must have an ID';
    });

if ($validator->failed()) {
    var_dump($validator->getErrors());
}
```

</details>

<p style="text-align:right">(<a href="#top">back to top</a>)</p>

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

<p style="text-align:right">(<a href="#top">back to top</a>)</p>

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

<p style="text-align:right">(<a href="#top">back to top</a>)</p>

## Requirements

- PHP 8.2+
- PHPUnit 11.0+

<p style="text-align:right">(<a href="#top">back to top</a>)</p>

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details on contributing to this project.

<p style="text-align:right">(<a href="#top">back to top</a>)</p>

## Contributors

See [CONTRIBUTORS.md](CONTRIBUTORS.md) for a list of contributors to this project.

<p style="text-align:right">(<a href="#top">back to top</a>)</p>

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

<p style="text-align:right">(<a href="#top">back to top</a>)</p>

## Credits

- [Fallega HQ](https://github.com/fallegahq)
- [Yassine Selmi](https://github.com/selmiyassine)
- [All Contributors](CONTRIBUTORS.md)

## Made with 💕

<!-- MARKDOWN LINKS & IMAGES -->
[composer-version]: https://img.shields.io/packagist/v/fallegahq/json-test-utils.svg?style=for-the-badge
[composer-shield]: https://img.shields.io/packagist/dt/fallegahq/json-test-utils.svg?style=for-the-badge
[build-shield]: https://img.shields.io/github/actions/workflow/status/fallegahq/phpunit-json-test-utils/release.yml?style=for-the-badge
[build-url]: https://github.com/fallegahq/phpunit-json-test-utils/actions/workflows/release.yml
[composer-url]: https://packagist.org/packages/fallegahq/json-test-utils
[contributors-shield]: https://img.shields.io/github/contributors/FallegaHQ/phpunit-json-test-utils.svg?style=for-the-badge
[contributors-url]: https://github.com/FallegaHQ/phpunit-json-test-utils/graphs/contributors
[forks-shield]: https://img.shields.io/github/forks/FallegaHQ/phpunit-json-test-utils.svg?style=for-the-badge
[forks-url]: https://github.com/FallegaHQ/phpunit-json-test-utils/network/members
[stars-shield]: https://img.shields.io/github/stars/FallegaHQ/phpunit-json-test-utils.svg?style=for-the-badge
[stars-url]: https://github.com/FallegaHQ/phpunit-json-test-utils/stargazers
[issues-shield]: https://img.shields.io/github/issues/FallegaHQ/phpunit-json-test-utils.svg?style=for-the-badge
[issues-url]: https://github.com/FallegaHQ/phpunit-json-test-utils/issues
[license-shield]: https://img.shields.io/github/license/FallegaHQ/phpunit-json-test-utils.svg?style=for-the-badge&logo=MIT
[license-url]: https://github.com/FallegaHQ/phpunit-json-test-utils/blob/master/LICENSE

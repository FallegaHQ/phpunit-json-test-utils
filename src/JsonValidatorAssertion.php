<?php

/**
 * Made with love.
 */
declare(strict_types = 1);
namespace FallegaHQ\JsonTestUtils;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;

/**
 * Helper class for building fluent JSON assertions
 */
class JsonValidatorAssertion {
    private JsonValidator $validator;

    /**
     * @param array|string $json The JSON data to validate
     */
    public function __construct(array|string $json) {
        $this->validator = new JsonValidator($json);
    }

    /**
     * Assert that the JSON has the specified key
     *
     * @param string $key The key to check for
     *
     * @return $this For method chaining
     */
    public function hasKey(string $key): self {
        $this->validator->has($key);

        return $this;
    }

    /**
     * Assert that the JSON has all the specified keys
     *
     * @param array $keys The keys to check for
     *
     * @return $this For method chaining
     */
    public function hasKeys(array $keys): self {
        $this->validator->hasAll($keys);

        return $this;
    }

    /**
     * Assert that the JSON does not have the specified key
     *
     * @param string $key The key to check for absence
     *
     * @return $this For method chaining
     */
    public function notHasKey(string $key): self {
        $this->validator->hasNot($key);

        return $this;
    }

    /**
     * Assert that the JSON has any of the specified keys
     *
     * @param string ...$keys The keys to check for
     *
     * @return $this For method chaining
     */
    public function hasAnyKey(string ...$keys): self {
        $this->validator->hasAnyOf(...$keys);

        return $this;
    }

    /**
     * Assert that the value at the path equals the expected value
     *
     * @param string $key   The key to check
     * @param mixed  $value The expected value
     *
     * @return $this For method chaining
     */
    public function equals(string $key, mixed $value): self {
        $this->validator->hasWithValue($key, $value);

        return $this;
    }

    /**
     * Assert that the value at the path is of the specified type
     *
     * @param string $key  The key to check
     * @param string $type The expected type
     *
     * @return $this For method chaining
     */
    public function isType(string $key, string $type): self {
        $this->validator->isType($key, $type);

        return $this;
    }

    /**
     * Assert that the value at the path is in the list of allowed values
     *
     * @param string       $key           The key to check
     * @param array|string $allowedValues Array of allowed values or enum class name
     *
     * @return $this For method chaining
     */
    public function in(string $key, array|string $allowedValues): self {
        $this->validator->isIn($key, $allowedValues);

        return $this;
    }

    /**
     * Assert that the value at the path matches a regex pattern
     *
     * @param string $key     The key to check
     * @param string $pattern The regex pattern
     *
     * @return $this For method chaining
     */
    public function matches(string $key, string $pattern): self {
        $this->validator->matchesRegex($key, $pattern);

        return $this;
    }

    /**
     * Assert that the value at the path is a valid email
     *
     * @param string $key The key to check
     *
     * @return $this For method chaining
     */
    public function isEmail(string $key): self {
        $this->validator->isEmail($key);

        return $this;
    }

    /**
     * Assert that the value at the path is a valid URL
     *
     * @param string $key The key to check
     *
     * @return $this For method chaining
     */
    public function isUrl(string $key): self {
        $this->validator->isURL($key);

        return $this;
    }

    /**
     * Assert that the value at the path is not empty
     *
     * @param string $key The key to check
     *
     * @return $this For method chaining
     */
    public function notEmpty(string $key): self {
        $this->validator->isNotEmpty($key);

        return $this;
    }

    /**
     * Assert that the value at the path has a length within the specified range
     *
     * @param string   $key   The key to check
     * @param int|null $exact Exact length required (or null if using min/max)
     * @param int|null $min   Minimum length (inclusive)
     * @param int|null $max   Maximum length (inclusive)
     *
     * @return $this For method chaining
     */
    public function hasLength(string $key, ?int $exact = null, ?int $min = null, ?int $max = null): self {
        $this->validator->hasLength($key, $exact, $min, $max);

        return $this;
    }

    /**
     * Assert the entire JSON structure against a schema
     *
     * @param array $schema The schema definition
     *
     * @return $this For method chaining
     */
    public function matchesSchema(array $schema): self {
        $this->validator->passesSchema('', $schema);

        return $this;
    }

    /**
     * Run all the validations and assert that they pass
     *
     * @param string|null $message Optional custom error message
     *
     * @throws AssertionFailedError if validation fails
     */
    public function assert(?string $message = null): void {
        $validationPassed = $this->validator->validated();
        $errors           = $this->validator->getErrors();

        $errorMsg         = $message ?? $this->formatErrors($errors);

        Assert::assertTrue($validationPassed, $errorMsg);
    }

    /**
     * Assert that the value at the path passes a custom condition
     *
     * @param string      $key      The key to check
     * @param callable    $callback Function that returns true if valid
     * @param string|null $message  Optional custom error message
     *
     * @return $this For method chaining
     */
    public function passes(string $key, callable $callback, ?string $message = null): self {
        $this->validator->passes($key, $callback, $message);

        return $this;
    }

    /**
     * Format validator errors into a readable string
     *
     * @param array $errors The errors from JsonValidator
     *
     * @return string Formatted error message
     */
    private function formatErrors(array $errors): string {
        if (empty($errors)) {
            return 'Unknown validation error';
        }

        $formattedErrors = [];
        foreach ($errors as $messages) {
            foreach ($messages as $message) {
                $formattedErrors[] = "{$message}";
            }
        }

        return "JSON validation failed:\n".implode(PHP_EOL, $formattedErrors);
    }
}

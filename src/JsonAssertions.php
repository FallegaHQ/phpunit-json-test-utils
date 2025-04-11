<?php

/**
 * Made with love.
 */

declare(strict_types = 1);
namespace FallegaHQ\JsonTestUtils;

use PHPUnit\Framework\Assert;

/**
 * A trait that provides JSON assertion methods for PHPUnit tests
 * Works with JsonValidator to provide fluent, expressive assertions
 */
trait JsonAssertions {
    /**
     * Assert that a JSON string or array is valid according to specified validations
     *
     * @param array|string $json The JSON data to validate
     *
     * @return JsonValidatorAssertion A fluent validator assertion builder
     */
    protected function assertValidJson(array|string $json): JsonValidatorAssertion {
        return new JsonValidatorAssertion($json);
    }

    /**
     * Assert that a JSON string or array has a specific key
     *
     * @param array|string $json    The JSON data to validate
     * @param string       $key     The key to check for (dot notation supported)
     * @param string|null  $message Optional custom error message
     */
    protected function assertJsonHasKey(array|string $json, string $key, ?string $message = null): void {
        $validator = new JsonValidator($json);
        $validator->has($key);

        Assert::assertTrue($validator->passes(), $message ?? $this->formatValidatorErrors($validator->errors()));
    }

    /**
     * Assert that a JSON string or array doesn't have a specific key
     *
     * @param array|string $json    The JSON data to validate
     * @param string       $key     The key to check for (dot notation supported)
     * @param string|null  $message Optional custom error message
     */
    protected function assertJsonNotHasKey(array|string $json, string $key, ?string $message = null): void {
        $validator = new JsonValidator($json);
        $validator->hasNot($key);

        Assert::assertTrue($validator->passes(), $message ?? $this->formatValidatorErrors($validator->errors()));
    }

    /**
     * Assert that a JSON string or array contains exactly the specified value at path
     *
     * @param array|string $json          The JSON data to validate
     * @param string       $key           The key to check (dot notation supported)
     * @param mixed        $expectedValue The expected value
     * @param string|null  $message       Optional custom error message
     */
    protected function assertJsonEquals(array|string $json, string $key, mixed $expectedValue, ?string $message = null): void {
        $validator = new JsonValidator($json);
        $validator->where($key, $expectedValue);

        Assert::assertTrue($validator->passes(), $message ?? $this->formatValidatorErrors($validator->errors()));
    }

    /**
     * Assert that a JSON string or array contains a value of specific type at path
     *
     * @param array|string $json         The JSON data to validate
     * @param string       $key          The key to check (dot notation supported)
     * @param string       $expectedType The expected type
     * @param string|null  $message      Optional custom error message
     */
    protected function assertJsonType(array|string $json, string $key, string $expectedType, ?string $message = null): void {
        $validator = new JsonValidator($json);
        $validator->whereType($key, $expectedType);

        Assert::assertTrue($validator->passes(), $message ?? $this->formatValidatorErrors($validator->errors()));
    }

    /**
     * Assert that a JSON string or array contains a value that passes a custom condition
     *
     * @param array|string $json      The JSON data to validate
     * @param string       $key       The key to check (dot notation supported)
     * @param callable     $condition The condition to check
     * @param string|null  $message   Optional custom error message
     */
    protected function assertJsonCondition(array|string $json, string $key, callable $condition, ?string $message = null): void {
        $validator = new JsonValidator($json);
        $validator->whereIs($key, $condition);

        Assert::assertTrue($validator->passes(), $message ?? $this->formatValidatorErrors($validator->errors()));
    }

    /**
     * Format validator errors into a readable string
     *
     * @param array $errors The errors from JsonValidator
     *
     * @return string Formatted error message
     */
    private function formatValidatorErrors(array $errors): string {
        if (empty($errors)) {
            return 'Unknown validation error';
        }

        $formattedErrors = [];
        foreach ($errors as $messages) {
            foreach ($messages as $message) {
                $formattedErrors[] = (string) ($message);
            }
        }

        return implode(PHP_EOL, $formattedErrors);
    }
}

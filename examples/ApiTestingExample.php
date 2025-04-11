<?php

/**
 * Made with love.
 */
namespace FallegaHQ\JsonTestUtils\Examples;

use FallegaHQ\JsonTestUtils\JsonAssertions;
use PHPUnit\Framework\TestCase;

class ApiTestingExample extends TestCase {
    use JsonAssertions;

    /**
     * Example of testing a REST API response
     *
     * This is a mocked example but demonstrates how you would test
     * real API responses in your application.
     *
     * @throws \JsonException
     */
    public function testApiEndpoint(): void {
        // In a real test, you might use Guzzle, Symfony HttpClient, or your framework's
        // testing tools to make an actual HTTP request.
        // Here we're simulating a response:
        $response = $this->getMockedApiResponse();

        // First, validate the structure
        $this->assertValidJson($response)
            ->hasKey('success')
            ->equals('success', true)
            ->hasKey('data')
            ->isType('data', 'array')
            ->hasKey('data.users')
            ->isType('data.users', 'array')
            ->assert('API response structure is invalid');

        // Then validate pagination details
        $this->assertValidJson($response)
            ->hasKey('data.pagination')
            ->isType('data.pagination.current_page', 'integer')
            ->isType('data.pagination.total_pages', 'integer')
            ->isType('data.pagination.total_items', 'integer')
            ->assert('Pagination information is missing or invalid');

        // Finally, validate individual users in the response
        $this->assertValidJson($response)
            ->passes('data.users', function ($users) {
                if (empty($users)) {
                    return 'Users array cannot be empty';
                }

                foreach ($users as $user) {
                    if (! isset($user['id']) || ! is_numeric($user['id'])) {
                        return 'Each user must have a numeric ID';
                    }

                    if (! isset($user['email']) || false === filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {
                        return 'Each user must have a valid email address';
                    }
                }

                return true;
            })
            ->assert('User data validation failed');
    }

    /**
     * Example of testing a JSON API error response
     *
     * @throws \JsonException
     */
    public function testApiErrorResponse(): void {
        // Simulate an error response from API
        $errorResponse = $this->getMockedErrorResponse();

        $this->assertValidJson($errorResponse)
            ->hasKey('success')
            ->equals('success', false)
            ->hasKey('error')
            ->isType('error.code', 'integer')
            ->isType('error.message', 'string')
            ->notEmpty('error.message')
            ->assert('Error response format is invalid');
    }

    /**
     * This method mocks a successful API response for testing purposes
     * In a real test, you would make an actual API request
     *
     * @throws \JsonException
     */
    private function getMockedApiResponse(): string {
        return json_encode([
            'success' => true,
            'data'    => [
                'users'      => [
                    [
                        'id'            => 1,
                        'name'          => 'Alice Johnson',
                        'email'         => 'alice@example.com',
                        'role'          => 'admin',
                        'registered_at' => '2024-12-15',
                    ],
                    [
                        'id'            => 2,
                        'name'          => 'Bob Smith',
                        'email'         => 'bob@example.com',
                        'role'          => 'user',
                        'registered_at' => '2025-01-20',
                    ],
                    [
                        'id'            => 3,
                        'name'          => 'Carol White',
                        'email'         => 'carol@example.com',
                        'role'          => 'editor',
                        'registered_at' => '2025-03-05',
                    ],
                ],
                'pagination' => [
                    'current_page' => 1,
                    'total_pages'  => 3,
                    'total_items'  => 27,
                ],
            ],
        ], JSON_THROW_ON_ERROR);
    }

    /**
     * This method mocks an error API response for testing purposes
     *
     * @throws \JsonException
     */
    private function getMockedErrorResponse(): string {
        return json_encode([
            'success' => false,
            'error'   => [
                'code'    => 404,
                'message' => 'Resource not found',
            ],
        ], JSON_THROW_ON_ERROR);
    }
}

<?php

/**
 * Made with love.
 */
namespace FallegaHQ\JsonTestUtils\Examples;

use FallegaHQ\JsonTestUtils\JsonAssertions;
use PHPUnit\Framework\TestCase;

class BasicUsage extends TestCase {
    use JsonAssertions;

    /**
     * Example of validating a simple JSON response
     */
    public function testSimpleJsonValidation(): void {
        // Sample JSON response (as string or already decoded array)
        $json = <<<'EOD'
            {
                        "status": "success",
                        "code": 200,
                        "data": {
                            "user": {
                                "id": 123,
                                "name": "John Doe",
                                "email": "john@example.com",
                                "is_active": true,
                                "tags": ["customer", "premium"]
                            }
                        }
                    }
            EOD;

        // Basic assertions using trait methods
        $this->assertJsonHasKey($json, 'status');
        $this->assertJsonEquals($json, 'status', 'success');
        $this->assertJsonType($json, 'code', 'integer');
        $this->assertJsonHasKey($json, 'data.user');
        $this->assertJsonType($json, 'data.user.tags', 'array');

        // Using a custom condition
        $this->assertJsonCondition($json, 'data.user.id', function ($value) {
            return $value > 0 && $value < 1_000;
        });
    }

    /**
     * Example of using fluent syntax for more readable tests
     */
    public function testFluentSyntax(): void {
        $json = <<<'EOD'
            {
                        "product": {
                            "id": "prod-123",
                            "name": "Premium Widget",
                            "price": 49.99,
                            "in_stock": true,
                            "categories": ["electronics", "gadgets"]
                        }
                    }
            EOD;

        // Fluent API offers a chainable, readable syntax
        $this->assertValidJson($json)
            ->hasKey('product')
            ->hasKey('product.name')
            ->equals('product.name', 'Premium Widget')
            ->isType('product.price', 'float')
            ->isType('product.in_stock', 'boolean')
            ->isType('product.categories', 'array')
            ->hasLength('product.categories', 2)  // Exactly 2 categories
            ->assert();
    }
}

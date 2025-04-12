<?php

/**
 * Made with love.
 */
namespace FallegaHQ\JsonTestUtils\Examples;

use FallegaHQ\JsonTestUtils\JsonAssertions;
use FallegaHQ\JsonTestUtils\JsonValidator;
use PHPUnit\Framework\TestCase;

class AdvancedExamples extends TestCase {
    use JsonAssertions;

    /**
     * Example of validating a complex API response with nested data
     */
    public function testApiResponseValidation(): void {
        // Sample API response with nested data structure
        $apiResponse = <<<'EOD'
            {
                        "meta": {
                            "status": "success",
                            "version": "1.0",
                            "timestamp": "2025-04-10T12:30:45Z"
                        },
                        "data": {
                            "orders": [
                                {
                                    "id": "order-1001",
                                    "customer_id": 5432,
                                    "amount": 129.99,
                                    "status": "shipped",
                                    "items": [
                                        {"product_id": "p-100", "quantity": 1, "price": 99.99},
                                        {"product_id": "p-101", "quantity": 2, "price": 15.00}
                                    ],
                                    "shipping_address": {
                                        "street": "123 Main St",
                                        "city": "Any town",
                                        "zip": "12345",
                                        "country": "US"
                                    }
                                }
                            ],
                            "pagination": {
                                "current_page": 1,
                                "total_pages": 5,
                                "items_per_page": 10
                            }
                        }
                    }
            EOD;

        $this->assertValidJson($apiResponse)
            // Check metadata
            ->hasKey('meta')
            ->equals('meta.status', 'success')
            ->isType('meta.timestamp', 'string')

            // Check data structure
            ->hasKey('data.orders')
            ->isType('data.orders', 'array')
            ->hasLength('data.orders', null, 1) // At least 1 order

             // Check first order properties
            ->isType('data.orders.0.id', 'string')
            ->isType('data.orders.0.amount', 'float')
            ->in('data.orders.0.status', ['pending', 'processing', 'shipped', 'delivered'])

            // Check items in first order
            ->isType('data.orders.0.items', 'array')
            ->passes('data.orders.0.items', function ($items) {
                return count($items) > 0 ? true : 'Order should have at least one item';
            })

            // Check shipping address
            ->hasKeys(['data.orders.0.shipping_address.street',
                'data.orders.0.shipping_address.city',
                'data.orders.0.shipping_address.country'])

            // Check pagination
            ->hasKey('data.pagination')
            ->isType('data.pagination.current_page', 'integer')
            ->isType('data.pagination.total_pages', 'integer')
            ->assert();
    }

    /**
     * Example of schema-based validation
     */
    public function testSchemaValidation(): void {
        $json   = <<<'EOD'
            {
                        "article": {
                            "id": 42,
                            "title": "How to Test JSON in PHP",
                            "slug": "how-to-test-json-in-php",
                            "content": "This is a comprehensive guide...",
                            "published_at": "2025-03-15",
                            "author": {
                                "id": 5,
                                "name": "Jane Smith",
                                "email": "jane@example.com"
                            },
                            "tags": ["php", "testing", "json"]
                        }
                    }
            EOD;

        // Define a schema for validation
        $schema = [
            'article'              => [
                'type'     => 'array',
                'required' => true,
            ],
            'article.id'           => [
                'type'     => 'integer',
                'required' => true,
            ],
            'article.title'        => [
                'type'     => 'string',
                'required' => true,
            ],
            'article.slug'         => [
                'type'     => 'string',
                'required' => true,
            ],
            'article.published_at' => [
                'type'     => 'string',
                'required' => true,
            ],
            'article.author'       => [
                'type'     => 'array',
                'required' => true,
            ],
            'article.author.email' => [
                'type'     => 'string',
                'required' => true,
            ],
            'article.tags'         => [
                'type'     => 'array',
                'required' => true,
            ],
        ];

        $this->assertValidJson($json)
            ->matchesSchema($schema)
            // Additional specific checks
            ->isEmail('article.author.email')
            ->matches('article.slug', '/^[a-z0-9-]+$/')
            ->hasLength('article.tags', null, 1) // At least one tag
            ->assert();
    }

    /**
     * Example using direct JsonValidator for more complex validation
     *
     * @throws \JsonException
     */
    public function testDirectValidatorUsage(): void {
        $json      = <<<'EOD'
            {
                        "settings": {
                            "notifications": {
                                "email": true,
                                "push": false,
                                "sms": true
                            },
                            "privacy": {
                                "share_data": false,
                                "public_profile": true
                            }
                        }
                    }
            EOD;

        // Using the JsonValidator directly for more control
        $validator = new JsonValidator($json);

        // First level validation
        $validator->has('settings');
        $validator->isType('settings', 'array');

        // Validate notification settings
        $validator->has('settings.notifications');
        $validator->isType('settings.notifications.email', 'boolean');
        $validator->isType('settings.notifications.push', 'boolean');
        $validator->isType('settings.notifications.sms', 'boolean');

        // Validate privacy settings
        $validator->has('settings.privacy');
        $validator->isType('settings.privacy.share_data', 'boolean');
        $validator->isType('settings.privacy.public_profile', 'boolean');

        // Advanced: custom logic check across multiple values
        $validator->passes('settings', function ($settings) {
            // Example: at least one notification type must be enabled
            $notifications = $settings['notifications'] ?? [];
            $hasOneEnabled = false;

            foreach ($notifications as $enabled) {
                if (true === $enabled) {
                    $hasOneEnabled = true;
                    break;
                }
            }

            return $hasOneEnabled ? true : 'At least one notification must be enabled';
        });

        self::assertTrue($validator->validated(), 'Validation failed: '.json_encode($validator->getErrors(), JSON_THROW_ON_ERROR));
    }
}

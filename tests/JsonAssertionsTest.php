<?php

/**
 * Made with love.
 */

declare(strict_types = 1);
namespace FallegaHQ\JsonTestUtils\Tests;

use FallegaHQ\JsonTestUtils\JsonAssertions;
use FallegaHQ\JsonTestUtils\JsonValidator;
use FallegaHQ\JsonTestUtils\JsonValidatorAssertion;
use JsonException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(JsonAssertions::class)]
#[CoversClass(JsonValidator::class)]
#[CoversClass(JsonValidatorAssertion::class)]
class JsonAssertionsTest extends TestCase {
    use JsonAssertions;
    private array $testData = [
        'id'         => 123,
        'name'       => 'Test User',
        'email'      => 'test@example.com',
        'active'     => true,
        'tags'       => [
            'php',
            'testing',
            'json',
        ],
        'profile'    => [
            'age'     => 30,
            'city'    => 'Test City',
            'website' => 'https://example.com',
        ],
        'created_at' => '2023-01-01',
        'updated_at' => null,
    ];
    private string $testJson;

    /**
     * @throws JsonException
     */
    protected function setUp(): void {
        parent::setUp();
        $this->testJson = json_encode($this->testData, flags: JSON_THROW_ON_ERROR);
    }

    public function testAssertJsonHasKey(): void {
        $this->assertJsonHasKey($this->testData, 'name');
        $this->assertJsonHasKey($this->testData, 'profile.city');
        $this->assertJsonHasKey($this->testJson, 'tags');

        $this->assertValidJson($this->testData)
            ->hasKey('name')
            ->hasKey('profile.city')
            ->assert();
    }

    public function testAssertJsonNotHasKey(): void {
        $this->assertJsonNotHasKey($this->testData, 'foo');
        $this->assertJsonNotHasKey($this->testData, 'profile.country');

        $this->assertValidJson($this->testData)
            ->notHasKey('foo')
            ->notHasKey('profile.country')
            ->assert();
    }

    public function testAssertJsonEquals(): void {
        $this->assertJsonEquals($this->testData, 'id', 123);
        $this->assertJsonEquals($this->testData, 'name', 'Test User');
        $this->assertJsonEquals($this->testData, 'profile.city', 'Test City');

        $this->assertValidJson($this->testData)
            ->equals('id', 123)
            ->equals('name', 'Test User')
            ->equals('profile.city', 'Test City')
            ->assert();
    }

    public function testAssertJsonType(): void {
        $this->assertJsonType($this->testData, 'id', 'int');
        $this->assertJsonType($this->testData, 'name', 'string');
        $this->assertJsonType($this->testData, 'active', 'bool');
        $this->assertJsonType($this->testData, 'tags', 'array');
        $this->assertJsonType($this->testData, 'profile', 'array');
        $this->assertJsonType($this->testData, 'updated_at', 'null');

        $this->assertValidJson($this->testData)
            ->isType('id', 'int')
            ->isType('name', 'string')
            ->isType('active', 'bool')
            ->isType('tags', 'array')
            ->assert();
    }

    public function testAssertJsonCondition(): void {
        $this->assertJsonCondition($this->testData, 'id', function ($value) {
            return $value > 100;
        });

        $this->assertValidJson($this->testData)
            ->passes('id', function ($value) {
                return $value > 100;
            }, 'ID must be greater than 100')
            ->assert();
    }

    public function testFluentInterfaceWithMultipleAssertions(): void {
        $this->assertValidJson($this->testData)
            ->hasKey('id')
            ->isType('id', 'int')
            ->equals('id', 123)
            ->hasKey('profile')
            ->isType('profile', 'array')
            ->hasKey('profile.website')
            ->isUrl('profile.website')
            ->hasKeys([
                'name',
                'email',
                'active',
            ], )
            ->notHasKey('non_existent_key')
            ->isEmail('email')
            ->notEmpty('tags')
            ->hasLength('tags', 3)
            ->assert();
    }

    public function testJsonSchemaValidation(): void {
        $schema = [
            'id'         => 'integer',
            'name'       => 'string',
            'email'      => function (JsonValidator $validator, string $key) {
                $validator->isEmail($key);
            },
            'active'     => 'boolean',
            'tags'       => [
                'type'      => 'array',
                'minLength' => 1,
            ],
            'profile'    => [
                'age'     => 'integer',
                'city'    => 'string',
                'website' => function (JsonValidator $validator, string $key) {
                    $validator->isURL($key);
                },
            ],
            'created_at' => [
                'type'    => 'string',
                'pattern' => '/^\d{4}-\d{2}-\d{2}$/',
            ],
            'updated_at' => 'null',
        ];

        $this->assertValidJson($this->testData)
            ->matchesSchema($schema)
            ->assert();
    }

    public function testJsonContainsValues(): void {
        $this->assertValidJson($this->testData)
            ->in('tags.0', [
                'php',
                'javascript',
                'python',
            ], )
            ->assert();
    }

    public function testJsonRegexMatches(): void {
        $this->assertValidJson($this->testData)
            ->matches('email', '/^.+@.+\..+$/')
            ->matches('created_at', '/^\d{4}-\d{2}-\d{2}$/')
            ->assert();
    }
}

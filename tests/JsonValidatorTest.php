<?php

/**
 * Made with love.
 */

declare(strict_types = 1);
namespace FallegaHQ\JsonTestUtils\Tests;

use FallegaHQ\JsonTestUtils\JsonValidationException;
use FallegaHQ\JsonTestUtils\JsonValidator as Validator;
use JsonException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;
use Throwable;

enum UserStatus {
    case Active;
    case Pending;
    case Suspended;
    case Deleted;
}

enum UserRole: string {
    case Admin      = 'admin';
    case Editor     = 'editor';
    case Author     = 'author';
    case Subscriber = 'subscriber';
}

enum Status: string {
    case Active   = 'active';
    case Inactive = 'inactive';
}

#[CoversClass(Validator::class)]
#[CoversClass(JsonValidationException::class)]
class JsonValidatorTest extends TestCase {
    private string $validJson = <<<'EOD'
        {
            "name":"John",
            "age":30,
            "email":"john@example.com",
            "roles":[
                "admin",
                "user"
            ],
            "user":{
                "role":"admin"
            },
            "settings":{
                "notifications":true
            }
        }
        EOD;
    private mixed $validArray;
    private array $testData   = [
        'user' => [
            'id'          => 1,
            'name'        => 'John Doe',
            'email'       => 'john@example.com',
            'status'      => 'Active',
            'role'        => 'editor',
            'permissions' => [
                'read',
                'write',
            ],
        ],
    ];
    private array $nestedData = [
        'user'     => [
            'id'       => 123,
            'profile'  => [
                'name'    => 'John Doe',
                'email'   => 'john@example.com',
                'contact' => [
                    'phone'   => '123-456-7890',
                    'address' => [
                        'street'  => '123 Main St',
                        'city'    => 'New York',
                        'country' => 'USA',
                    ],
                ],
            ],
            'settings' => [
                'notifications' => true,
                'theme'         => 'dark',
            ],
            'roles'    => [
                'user',
                'editor',
            ],
        ],
        'posts'    => [
            [
                'id'      => 1,
                'title'   => 'First Post',
                'content' => 'Lorem ipsum',
            ],
            [
                'id'      => 2,
                'title'   => 'Second Post',
                'content' => 'Dolor sit amet',
            ],
        ],
        'metadata' => [
            'created_at' => '2023-01-01',
            'version'    => 2.5,
        ],
    ];

    /**
     * @throws JsonException
     */
    protected function setUp(): void {
        parent::setUp();
        $this->validArray = json_decode($this->validJson, true, 512, JSON_THROW_ON_ERROR);
    }

    public function testValidateJsonString(): void {
        $this->expectNotToPerformAssertions();

        try {
            Validator::validator($this->validJson);
        }
        catch (Throwable) {
            self::fail('A exception has been thrown.');
        }

    }

    public function testValidateInvalidJsonThrowsException(): void {
        $this->expectException(JsonValidationException::class);
        Validator::validator('{invalid:json}');
    }

    public function testPassesWithValidCondition(): void {
        $result = Validator::validator($this->validArray)
            ->passes('age', fn ($age) => $age >= 18)
            ->validated();

        static::assertTrue($result);
    }

    public function testPassesWithInvalidCondition(): void {
        $validator = Validator::validator($this->validArray);
        $result    = $validator->passes('age', fn ($age) => $age > 100)
            ->validated();

        static::assertFalse($result);
    }

    public function testPassesWithCustomMessage(): void {
        $validator = Validator::validator($this->validArray)
            ->passes('age', fn ($age) => $age > 100, 'Age must be greater than 100');

        $errors    = $validator->getErrors();

        static::assertArrayHasKey('age', $errors);
        static::assertContains('Age must be greater than 100', $errors['age']);
    }

    public function testHasWithValueWithValidValue(): void {
        $result = Validator::validator($this->validArray)
            ->hasWithValue('name', 'John')
            ->validated();

        static::assertTrue($result);
    }

    public function testHasWithValueWithInvalidValue(): void {
        $result = Validator::validator($this->validArray)
            ->hasWithValue('name', 'Jane')
            ->validated();

        static::assertFalse($result);
    }

    public function testIsTypeWithValidType(): void {
        $result = Validator::validator($this->validArray)
            ->isType('name', 'string')
            ->isType('age', 'integer')
            ->isType('roles', 'array')
            ->isType('settings', 'array')
            ->validated();

        static::assertTrue($result);
    }

    public function testIsTypeWithInvalidType(): void {
        $result = Validator::validator($this->validArray)
            ->isType('name', 'integer')
            ->validated();

        static::assertFalse($result);
    }

    public function testIsTypeWithValidNullAndObjectType(): void {
        $validator = new Validator([
            'obj'  => new stdClass(),
            'none' => null,
        ], );
        $result    = $validator->isType('obj', 'object')
            ->isType('obj', stdClass::class)
            ->isType('none', 'null')
            ->validated();

        static::assertTrue($result);
    }

    public function testIsTypeWithMissingKey(): void {
        $result = Validator::validator($this->validArray)
            ->isType('nonexistent', 'string')
            ->validated();

        static::assertFalse($result);
    }

    public function testhasTypedItems(): void {
        $result = Validator::validator($this->validArray)
            ->hasTypedItems([
                'name'  => 'string',
                'age'   => 'integer',
                'roles' => 'array',
            ])
            ->validated();

        static::assertTrue($result);
    }

    public function testhasTypedItemsWithInvalidType(): void {
        $result = Validator::validator($this->validArray)
            ->hasTypedItems([
                'name' => 'string',
                'age'  => 'string',
            ])
            ->validated();

        static::assertFalse($result);
    }

    public function testOptional(): void {
        $result    = Validator::validator($this->validArray)
            ->optional('nonexistent', 'doesnt-exist')
            ->validated();

        static::assertTrue($result);

        $validator = Validator::validator($this->validArray);
        $result    = $validator->optional('age', 30)
            ->validated();

        static::assertTrue($result);
    }

    public function testOptionalWithType(): void {
        $result    = Validator::validator($this->validArray)
            ->optionalWithType('nonexistent', 'string')
            ->validated();

        static::assertTrue($result);

        $validator = Validator::validator($this->validArray);
        $result    = $validator->optionalWithType('age', 'int')
            ->validated();

        static::assertTrue($result);
    }

    public function testHasNot(): void {
        $result = Validator::validator($this->validArray)
            ->hasNot('nonexistent')
            ->validated();

        static::assertTrue($result);
    }

    public function testHasNotWithExistingKey(): void {
        $result = Validator::validator($this->validArray)
            ->hasNot('name')
            ->validated();

        static::assertFalse($result);
    }

    public function testIsInWithValidValue(): void {
        $result = Validator::validator($this->validArray)
            ->isIn('name', [
                'John',
                'Jane',
                'Alice',
            ], )
            ->validated();

        static::assertTrue($result);
    }

    public function testIsInWithInvalidValue(): void {
        $result = Validator::validator($this->validArray)
            ->isIn('name', [
                'Jane',
                'Alice',
            ], )
            ->validated();

        static::assertFalse($result);
    }

    public function testIsInWithMissingKey(): void {
        $result = Validator::validator($this->validArray)
            ->isIn('nonexistent', [
                'value1',
                'value2',
            ], )
            ->validated();

        static::assertFalse($result);
    }

    public function testIsInWithEnumClass(): void {
        $mockEnum      = new class() {
            public const VALUE1 = 'John';
            public const VALUE2 = 'Jane';

            /**
             * @return object[]
             */
            public static function cases(): array {
                return [
                    (object) [
                        'value' => self::VALUE1,
                    ],
                    (object) [
                        'value' => self::VALUE2,
                    ],
                ];
            }
        };

        $mockEnumClass = get_class($mockEnum);

        $validator     = Validator::validator($this->validArray);

        $result        = $validator->isIn('name', $mockEnumClass)
            ->validated();

        static::assertTrue($result);
    }

    public function testHas(): void {
        $result = Validator::validator($this->validArray)
            ->has('name')
            ->validated();

        static::assertTrue($result);
    }

    public function testHasWithMissingKey(): void {
        $result = Validator::validator($this->validArray)
            ->has('nonexistent')
            ->validated();

        static::assertFalse($result);
    }

    public function testHasAll(): void {
        $result = Validator::validator($this->validArray)
            ->hasAll([
                'name',
                'age',
                'email',
            ], )
            ->validated();

        static::assertTrue($result);
    }

    public function testHasAllWithMissingKey(): void {
        $validator = Validator::validator($this->validArray);
        $result    = $validator->hasAll([
            'name',
            'nonexistent',
        ], )
            ->validated();

        static::assertFalse($result);
    }

    public function testHasNoneOf(): void {
        $result = Validator::validator($this->validArray)
            ->hasNoneOf([
                'nonexistent1',
                'nonexistent2',
            ], )
            ->validated();

        static::assertTrue($result);
    }

    public function testHasNoneOofWithExistingKey(): void {
        $result = Validator::validator($this->validArray)
            ->hasNoneOf([
                'nonexistent',
                'name',
            ], )
            ->validated();

        static::assertFalse($result);
    }

    public function testHasAnyOf(): void {
        $result = Validator::validator($this->validArray)
            ->hasAnyOf('name', 'nonexistent')
            ->validated();

        static::assertTrue($result);
    }

    public function testHasAnyOfWithAllMissingKeys(): void {
        $result = Validator::validator($this->validArray)
            ->hasAnyOf('nonexistent1', 'nonexistent2')
            ->validated();

        static::assertFalse($result);
    }

    public function testIsFile(): void {
        $tempFile = tempnam(sys_get_temp_dir(), 'test');

        $data     = [
            'file_path' => $tempFile,
        ];
        $result   = Validator::validator($data)
            ->isFile('file_path')
            ->validated();

        static::assertTrue($result);

        unlink($tempFile);
    }

    public function testIsFileWithNonexistentFile(): void {
        $data   = [
            'file_path' => '/path/to/nonexistent/file.txt',
        ];
        $result = Validator::validator($data)
            ->isFile('file_path')
            ->validated();

        static::assertFalse($result);
    }

    public function testIsFileWithNonStringValue(): void {
        $data   = [
            'file_path' => 123,
        ];
        $result = Validator::validator($data)
            ->isFile('file_path')
            ->validated();

        static::assertFalse($result);
    }

    public function testIsFileWithMissingKey(): void {
        $result = Validator::validator($this->validArray)
            ->isFile('nonexistent')
            ->validated();

        static::assertFalse($result);
    }

    public function testIsFileWithExistenceFlagOff(): void {
        $data   = [
            'file_path' => '/path/to/nonexistent/file.txt',
        ];
        $result = Validator::validator($data)
            ->isFile('file_path', false)
            ->validated();

        static::assertTrue($result);
    }

    public function tesPasses(): void {
        $result = Validator::validator($this->validArray)
            ->passes('email', function ($email) {
                return false !== filter_var($email, FILTER_VALIDATE_EMAIL) ? true : 'Invalid email format';
            }, )
            ->validated();

        static::assertTrue($result);
    }

    public function tesPassesWithInvalidValue(): void {
        $data   = [
            'email' => 'not-an-email',
        ];
        $result = Validator::validator($data)
            ->passes('email', function ($email) {
                return false !== filter_var($email, FILTER_VALIDATE_EMAIL) ? true : 'Invalid email format';
            }, )
            ->validated();

        static::assertFalse($result);
    }

    public function tesPassesWithMissingKey(): void {
        $result = Validator::validator($this->validArray)
            ->passes('nonexistent', function () {
                return true;
            }, )
            ->validated();

        static::assertFalse($result);
    }

    public function testMatchesRegex(): void {
        $result = Validator::validator($this->validArray)
            ->matchesRegex('email', '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/')
            ->validated();

        static::assertTrue($result);
    }

    public function testMatchesRegexWithInvalidValue(): void {
        $data   = [
            'email' => 'not-an-email',
        ];
        $result = Validator::validator($data)
            ->matchesRegex('email', '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/')
            ->validated();

        static::assertFalse($result);
    }

    public function testMatchesRegexWithMatchAll(): void {
        $data   = [
            'text' => 'one two three',
        ];
        $result = Validator::validator($data)
            ->matchesRegex('text', '/\w+/', true)
            ->validated();

        static::assertTrue($result);
    }

    public function testMatchesRegexWithMissingKey(): void {
        $result = Validator::validator($this->validArray)
            ->matchesRegex('nonexistent', '/pattern/')
            ->validated();

        static::assertFalse($result);
    }

    public function testMatchesRegexWithNonStringValue(): void {
        $result = Validator::validator($this->validArray)
            ->matchesRegex('age', '/pattern/')
            ->validated();

        static::assertFalse($result);
    }

    public function testValidated(): void {
        $result = Validator::validator($this->validArray)
            ->has('name')
            ->validated();

        static::assertTrue($result);
    }

    public function testFailed(): void {
        $result = Validator::validator($this->validArray)
            ->has('nonexistent')
            ->failed();

        static::assertTrue($result);
    }

    public function testErrors(): void {
        $validator = Validator::validator($this->validArray)
            ->has('nonexistent')
            ->hasWithValue('name', 'Jane');

        $validator->validated();
        $errors    = $validator->getErrors();

        static::assertArrayHasKey('nonexistent', $errors);
        static::assertArrayHasKey('name', $errors);
    }

    public function testGetValidData(): void {
        $validator = Validator::validator($this->validArray)
            ->has('name');

        $data      = $validator->getValidData();

        static::assertEquals($this->validArray, $data);
    }

    public function testGetValidDataWithFailedValidation(): void {
        $validator = Validator::validator($this->validArray)
            ->has('nonexistent');

        $data      = $validator->getValidData();

        static::assertNull($data);
    }

    /**
     * @throws JsonException
     */
    public function testValidatedStrict(): void {
        $validator = Validator::validator($this->validArray)
            ->has('name');

        static::assertTrue($validator->validatedStrict());
    }

    /**
     * @throws JsonException
     */
    public function testValidateStrictThrowsException(): void {
        $this->expectException(JsonValidationException::class);

        $validator = Validator::validator($this->validArray)
            ->has('nonexistent');

        $validator->validatedStrict();
    }

    public function testChainedValidation(): void {
        $result = Validator::validator($this->validArray)
            ->has('name')
            ->hasWithValue('name', 'John')
            ->isType('age', 'integer')
            ->passes('age', fn ($age) => $age >= 18)
            ->isIn('email', [
                'john@example.com',
                'jane@example.com',
            ], )
            ->passes('email', fn ($email) => false !== filter_var($email, FILTER_VALIDATE_EMAIL))
            ->matchesRegex('email', '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/')
            ->hasAll([
                'name',
                'age',
                'email',
            ], )
            ->hasNoneOf([
                'nonexistent1',
                'nonexistent2',
            ], )
            ->hasAnyOf('name', 'nonexistent')
            ->validated();

        static::assertTrue($result);
    }

    public function testIsInWithPureEnum(): void {
        $data                   = $this->testData;
        $data['user']['status'] = UserStatus::Active->name;

        $validator              = Validator::validator($data);
        $result                 = $validator->isIn('user.status', UserStatus::class)
            ->validated();

        static::assertTrue($result);

        $data['user']['status'] = 'Unknown';

        $result                 = Validator::validator($data)
            ->isIn('user.status', UserStatus::class)
            ->validated();

        static::assertFalse($result);
    }

    public function testIsInWithBackedEnum(): void {
        $validator            = Validator::validator($this->testData);
        $result               = $validator->isIn('user.role', UserRole::class)
            ->validated();

        static::assertTrue($result);

        $data                 = $this->testData;
        $data['user']['role'] = 'guest';

        $result               = Validator::validator($data)
            ->isIn('user.role', UserRole::class)
            ->validated();

        static::assertFalse($result);
    }

    /**
     * @throws JsonException
     */
    public function testIsTypeWithEnum(): void {
        $json      = '{"status":"active"}';
        $validator = Validator::validator($json);

        $result    = $validator->isType('status', 'string')->validated();

        static::assertTrue($result);

        $json      = json_encode([
            'status' => 'inactive',
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);

        $result    = $validator->isIn('status', Status::class)
            ->validated();

        static::assertTrue($result);

        $json      = json_encode([
            'status' => 'pending',
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);

        $result    = $validator->isIn('status', Status::class)
            ->validated();

        static::assertFalse($result);
        $errors    = $validator->getErrors();
        static::assertArrayHasKey('status', $errors);
        static::assertContains("The 'status' must be one of: active, inactive", $errors['status']);
    }

    public function testComplexEnumValidation(): void {
        $data      = [
            'user' => [
                'id'       => 1,
                'name'     => 'John Doe',
                'status'   => UserStatus::Active->name,
                'role'     => UserRole::Editor->value,
                'verified' => true,
            ],
        ];

        $validator = Validator::validator($data);

        $result    = $validator->isType('user.id', 'integer')
            ->isType('user.name', 'string')
            ->isIn('user.status', UserStatus::class)
            ->isIn('user.role', UserRole::class)
            ->isType('user.verified', 'boolean')
            ->validated();

        static::assertTrue($result);
    }

    public function testHasWithDotNotation(): void {
        $validator = Validator::validator($this->nestedData);
        $result    = $validator->has('user.profile.name')
            ->validated();

        static::assertTrue($result);
    }

    public function testHasWithDeepNestedDotNotation(): void {
        $validator = Validator::validator($this->nestedData);
        $result    = $validator->has('user.profile.contact.address.country')
            ->validated();

        static::assertTrue($result);
    }

    public function testHasWithNonExistentNestedKey(): void {
        $validator = Validator::validator($this->nestedData);
        $result    = $validator->has('user.profile.contact.address.zipcode')
            ->validated();

        static::assertFalse($result);
    }

    public function testHasWithValueWithDotNotation(): void {
        $validator = Validator::validator($this->nestedData);
        $result    = $validator->hasWithValue('user.profile.name', 'John Doe')
            ->validated();

        static::assertTrue($result);
    }

    public function testIsTypeWithDotNotation(): void {
        $validator = Validator::validator($this->nestedData);
        $result    = $validator->isType('user.id', 'integer')
            ->isType('user.profile.name', 'string')
            ->isType('user.settings.notifications', 'boolean')
            ->isType('metadata.version', 'float')
            ->validated();

        static::assertTrue($result);
    }

    public function testIsInWithDotNotation(): void {
        $validator = Validator::validator($this->nestedData);
        $result    = $validator->isIn('user.settings.theme', [
            'light',
            'dark',
            'auto',
        ], )
            ->validated();

        static::assertTrue($result);
    }

    public function testPassesWithDotNotation(): void {
        $validator = Validator::validator($this->nestedData);
        $result    = $validator->passes('user.profile.name', function ($value) {
            return str_starts_with($value, 'John');
        }, )
            ->validated();

        static::assertTrue($result);
    }

    public function testMatchesRegexWithDotNotation(): void {
        $validator = Validator::validator($this->nestedData);
        $result    = $validator->matchesRegex('user.profile.email', '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/')
            ->validated();

        static::assertTrue($result);
    }

    public function testHasNotWithDotNotation(): void {
        $validator = Validator::validator($this->nestedData);
        $result    = $validator->hasNot('user.profile.contact.address.zipcode')
            ->validated();

        static::assertTrue($result);
    }

    public function testHasAllWithDotNotation(): void {
        $validator = Validator::validator($this->nestedData);
        $result    = $validator->hasAll([
            'user.profile.name',
            'user.profile.email',
            'user.settings.theme',
        ])
            ->validated();

        static::assertTrue($result);
    }

    public function testHasAnyOfWithDotNotation(): void {
        $validator = Validator::validator($this->nestedData);
        $result    = $validator->hasAnyOf('user.profile.nonexistent', 'user.settings.theme', 'user.unknown')
            ->validated();

        static::assertTrue($result);
    }

    public function tesPassesWithDotNotation(): void {
        $validator = Validator::validator($this->nestedData);
        $result    = $validator->passes('user.profile.email', function ($email) {
            return false !== filter_var($email, FILTER_VALIDATE_EMAIL) ? true : 'Invalid email format';
        }, )
            ->validated();

        static::assertTrue($result);
    }

    public function testComplexValidationWithDotNotation(): void {
        $validator = Validator::validator($this->nestedData);
        $result    = $validator->has('user.profile')
            ->isType('user.profile', 'array')
            ->isType('user.id', 'integer')
            ->hasWithValue('user.profile.contact.address.country', 'USA')
            ->matchesRegex('user.profile.contact.phone', '/^\d{3}-\d{3}-\d{4}$/')
            ->isIn('user.settings.theme', [
                'light',
                'dark',
            ], )
            ->passes('metadata.created_at', function ($date) {
                return false !== strtotime($date);
            }, )
            ->validated();

        static::assertTrue($result);
    }

    public function testValidationWithArrayElements(): void {
        $validator = Validator::validator($this->nestedData);
        $result    = $validator->has('posts.0.title')
            ->hasWithValue('posts.1.title', 'Second Post')
            ->validated();

        static::assertTrue($result);
    }

    public function testNestedPartialValidation(): void {
        $userProfile = $this->nestedData['user']['profile'];

        $validator   = Validator::validator([
            'profile' => $userProfile,
        ]);
        $result      = $validator->has('profile.name')
            ->has('profile.contact.address.city')
            ->hasWithValue('profile.contact.address.country', 'USA')
            ->validated();

        static::assertTrue($result);
    }

    /**
     * @throws JsonException
     */
    public function testArrayOfType(): void {
        $json      = json_encode([
            'array' => [
                1,
                2,
                3,
            ],
        ], flags: JSON_THROW_ON_ERROR, );
        $validator = Validator::validator($json);
        static::assertTrue($validator->arrayOfType('array', 'integer')
            ->validated(), );

        $json      = json_encode([
            'array' => [
                1,
                'string',
                3,
            ],
        ], flags: JSON_THROW_ON_ERROR, );
        $validator = Validator::validator($json);
        static::assertFalse($validator->arrayOfType('array', 'integer')
            ->validated(), );

        $json      = json_encode([
            'array' => 'string',
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertFalse($validator->arrayOfType('array', 'integer')
            ->validated(), );

        $json      = json_encode([
            'a' => [
                'b' => [
                    'array' => 'string',
                ],
            ],
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        $result    = $validator->arrayOfType('a.b', 'string')
            ->validated();
        static::assertTrue($result);

        $json      = json_encode([
            'a' => [
                'b' => [
                    'array' => 'string',
                ],
            ],
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        $result    = $validator->arrayOfType('a.b', 'int')
            ->validated();
        static::assertFalse($result);
    }

    /**
     * @throws JsonException
     */
    public function testIDate(): void {
        $json      = json_encode([
            'date' => '2023-05-15',
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertTrue($validator->isDate('date')
            ->validated(), );

        $json      = json_encode([
            'date' => '15/05/2023',
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertTrue($validator->isDate('date', 'd/m/Y')
            ->validated(), );

        $json      = json_encode([
            'date' => 'not-a-date',
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertFalse($validator->isDate('date')
            ->validated(), );
        static::assertArrayHasKey('date', $validator->getErrors());
    }

    /**
     * @throws JsonException
     */
    public function testIsEmail(): void {
        $json      = json_encode([
            'email' => 'test@example.com',
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertTrue($validator->isEmail('email')
            ->validated(), );

        $json      = json_encode([
            'email' => 'not-an-email',
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);

        $result    = $validator->isEmail('email')
            ->validated();
        static::assertFalse($result);
        static::assertArrayHasKey('email', $validator->getErrors());
        static::assertEmpty($validator->getValidData());
    }

    /**
     * @throws JsonException
     */
    public function testIsUrl(): void {
        $json      = json_encode([
            'url' => 'https://example.com',
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertTrue($validator->isURL('url')
            ->validated(), );

        $json      = json_encode([
            'url' => 'not-a-url',
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertFalse($validator->isURL('url')
            ->validated(), );
        static::assertArrayHasKey('url', $validator->getErrors());
    }

    /**
     * @throws JsonException
     */
    public function testIsIp(): void {
        $json      = json_encode([
            'ip' => '192.168.1.1',
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertTrue($validator->isIP('ip')
            ->validated(), );

        $json      = json_encode([
            'ip' => '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertTrue($validator->isIP('ip')
            ->validated(), );

        $json      = json_encode([
            'ip' => 'not-an-ip',
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertFalse($validator->isIP('ip')
            ->validated(), );
        static::assertArrayHasKey('ip', $validator->getErrors());

        $json      = json_encode([
            'ip' => '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertFalse($validator->isIP('ip', FILTER_FLAG_IPV4)
            ->validated(), );
    }

    /**
     * @throws JsonException
     */
    public function testIsBetween(): void {
        $json      = json_encode([
            'number' => 50,
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertTrue($validator->isBetween('number', 1, 100)
            ->validated(), );

        $json      = json_encode([
            'number' => 1,
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertTrue($validator->isBetween('number', 1, 100)
            ->validated(), );

        $json      = json_encode([
            'number' => 101,
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertFalse($validator->isBetween('number', 1, 100)
            ->validated(), );
        static::assertArrayHasKey('number', $validator->getErrors());

        $json      = json_encode([
            'number' => 'string',
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertFalse($validator->isBetween('number', 1, 100)
            ->validated(), );
    }

    /**
     * @throws JsonException
     */
    public function testhasLength(): void {
        $json      = json_encode([
            'string' => 'hello',
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertTrue($validator->hasLength('string', 5)
            ->validated(), );

        $validator = Validator::validator($json);
        static::assertTrue($validator->hasLength('string', null, 3, 10)
            ->validated(), );

        $validator = Validator::validator($json);
        static::assertFalse($validator->hasLength('string', null, 10)
            ->validated(), );

        $validator = Validator::validator($json);
        static::assertFalse($validator->hasLength('string', null, null, 3)
            ->validated(), );

        $json      = json_encode([
            'array' => [
                1,
                2,
                3,
            ],
        ], flags: JSON_THROW_ON_ERROR, );
        $validator = Validator::validator($json);
        static::assertTrue($validator->hasLength('array', 3)
            ->validated(), );

        $json      = json_encode([
            'value' => 123,
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertFalse($validator->hasLength('value')
            ->validated(), );
    }

    /**
     * @throws JsonException
     */
    public function testContains(): void {
        $json      = json_encode([
            'string' => 'hello world',
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertTrue($validator->contains('string', 'world')
            ->validated(), );

        $validator = Validator::validator($json);
        static::assertTrue($validator->contains('string', 'WORLD', false)
            ->validated(), );

        $validator = Validator::validator($json);
        static::assertFalse($validator->contains('string', 'missing')
            ->validated(), );

        $json      = json_encode([
            'string' => 123,
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertFalse($validator->contains('string', 'world')
            ->validated(), );
    }

    /**
     * @throws JsonException
     */
    public function testPassesEach(): void {
        $json      = json_encode([
            'array' => [
                1,
                2,
                3,
            ],
        ], flags: JSON_THROW_ON_ERROR, );
        $validator = Validator::validator($json);
        $result    = $validator->passesEach('array', function ($item) {
            return $item > 0 ? true : 'Must be positive';
        })
            ->validated();
        static::assertTrue($result);

        $json      = json_encode([
            'array' => [
                1,
                -2,
                3,
            ],
        ], flags: JSON_THROW_ON_ERROR, );
        $validator = Validator::validator($json);
        $result    = $validator->passesEach('array', function ($item) {
            return $item > 0 ? true : 'Must be positive';
        })
            ->validated();
        static::assertFalse($result);
        static::assertArrayHasKey('array.1', $validator->getErrors());

        $json      = json_encode([
            'array' => 'string',
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        $result    = $validator->passesEach('array', function () {
            return true;
        })
            ->validated();
        static::assertFalse($result);
    }

    /**
     * @throws JsonException
     */
    public function testIsNotEmpty(): void {
        $json      = json_encode([
            'string' => 'hello',
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertTrue($validator->isNotEmpty('string')
            ->validated(), );

        $json      = json_encode([
            'string' => '',
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertFalse($validator->isNotEmpty('string')
            ->validated(), );

        $json      = json_encode([
            'array' => [
                1,
                2,
                3,
            ],
        ], flags: JSON_THROW_ON_ERROR, );
        $validator = Validator::validator($json);
        static::assertTrue($validator->isNotEmpty('array')
            ->validated(), );

        $json      = json_encode([
            'array' => [],
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertFalse($validator->isNotEmpty('array')
            ->validated(), );
    }

    /**
     * @throws JsonException
     */
    public function testPassesSchema(): void {
        $jsonData           = json_encode([
            'name'    => 'John',
            'email'   => 'john@example.com',
            'age'     => 30,
            'address' => [
                'street' => '123 Main St',
                'city'   => 'Any town',
            ],
        ], flags: JSON_THROW_ON_ERROR);

        $schema             = [
            'name'    => 'string',
            'email'   => 'string',
            'age'     => 'integer',
            'address' => [
                'street' => 'string',
                'city'   => 'string',
            ],
        ];

        $validator          = Validator::validator($jsonData);
        static::assertTrue($validator->passesSchema('', $schema)
            ->validated(), );

        $complexSchema      = [
            'name'    => [
                'type'      => 'string',
                'required'  => true,
                'minLength' => 2,
                'maxLength' => 50,
            ],
            'email'   => [
                'type'     => 'string',
                'required' => true,
                'pattern'  => '/^.+@.+\..+$/',
            ],
            'age'     => [
                'type'     => 'integer',
                'required' => true,
                'min'      => 18,
                'max'      => 120,
            ],
            'address' => [
                'street' => [
                    'type'     => 'string',
                    'required' => true,
                ],
                'city'   => [
                    'type'     => 'string',
                    'required' => true,
                ],
            ],
        ];

        $validator          = Validator::validator($jsonData);
        static::assertTrue($validator->passesSchema('', $complexSchema)
            ->validated(), );

        $schemaWithCallback = [
            'name'  => 'string',
            'email' => function (Validator $validator, string $key) {
                $validator->isEmail($key);
            },
        ];

        $validator          = Validator::validator($jsonData);
        static::assertTrue($validator->passesSchema('', $schemaWithCallback)
            ->validated(), );

        $jsonData           = json_encode([
            'name'  => 123,
            'email' => 'not-an-email',
            'age'   => 'thirty',
        ], flags: JSON_THROW_ON_ERROR);

        $validator          = Validator::validator($jsonData);
        static::assertFalse($validator->passesSchema('', $schema)
            ->validated(), );
        static::assertCount(3, $validator->getErrors());
    }
}

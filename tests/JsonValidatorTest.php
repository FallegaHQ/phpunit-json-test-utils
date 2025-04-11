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
        $this->validArray = json_decode($this->validJson, true, flags: JSON_THROW_ON_ERROR);
    }

    public function testValidateJsonString(): void {
        $this->expectNotToPerformAssertions();

        try {
            Validator::validator($this->validJson);
        }
        catch (Throwable) {
            self::fail('Case invalid has no email.');
        }

    }

    public function testValidateInvalidJsonThrowsException(): void {
        $this->expectException(JsonValidationException::class);
        Validator::validator('{invalid:json}');
    }

    public function testWhereIsWithValidCondition(): void {
        $result = Validator::validator($this->validArray)
            ->whereIs('age', fn ($age) => $age >= 18)
            ->passes();

        static::assertTrue($result);
    }

    public function testWhereIsWithInvalidCondition(): void {
        $result = Validator::validator($this->validArray)
            ->whereIs('age', fn ($age) => $age > 100)
            ->passes();

        static::assertFalse($result);
    }

    public function testWhereIsWithCustomMessage(): void {
        $validator = Validator::validator($this->validArray)
            ->whereIs('age', fn ($age) => $age > 100, 'Age must be greater than 100');

        $errors    = $validator->errors();

        static::assertArrayHasKey('age', $errors);
        static::assertContains('Age must be greater than 100', $errors['age']);
    }

    public function testWhereWithValidValue(): void {
        $result = Validator::validator($this->validArray)
            ->where('name', 'John')
            ->passes();

        static::assertTrue($result);
    }

    public function testWhereWithInvalidValue(): void {
        $result = Validator::validator($this->validArray)
            ->where('name', 'Jane')
            ->passes();

        static::assertFalse($result);
    }

    public function testWhereTypeWithValidType(): void {
        $result = Validator::validator($this->validArray)
            ->whereType('name', 'string')
            ->whereType('age', 'integer')
            ->whereType('roles', 'array')
            ->whereType('settings', 'array')
            ->passes();

        static::assertTrue($result);
    }

    public function testWhereTypeWithInvalidType(): void {
        $result = Validator::validator($this->validArray)
            ->whereType('name', 'integer')
            ->passes();

        static::assertFalse($result);
    }

    public function testWhereTypeWithValidNullAndObjectType(): void {
        $validator = new Validator([
            'obj'  => new stdClass(),
            'none' => null,
        ], );
        $result    = $validator->whereType('obj', 'object')
            ->whereType('obj', stdClass::class)
            ->whereType('none', 'null')
            ->passes();

        static::assertTrue($result);
    }

    public function testWhereTypeWithMissingKey(): void {
        $result = Validator::validator($this->validArray)
            ->whereType('nonexistent', 'string')
            ->passes();

        static::assertFalse($result);
    }

    public function testWhereAllTypes(): void {
        $result = Validator::validator($this->validArray)
            ->whereAllTypes([
                'name'  => 'string',
                'age'   => 'integer',
                'roles' => 'array',
            ])
            ->passes();

        static::assertTrue($result);
    }

    public function testWhereAllTypesWithInvalidType(): void {
        $result = Validator::validator($this->validArray)
            ->whereAllTypes([
                'name' => 'string',
                'age'  => 'string',
            ])
            ->passes();

        static::assertFalse($result);
    }

    public function testWhereOptional(): void {
        $result    = Validator::validator($this->validArray)
            ->whereOptional('nonexistent', 'doesnt-exist')
            ->passes();

        static::assertTrue($result);

        $validator = Validator::validator($this->validArray);
        $result    = $validator->whereOptional('age', 30)
            ->passes();

        static::assertTrue($result);
    }

    public function testWhereOptionalType(): void {
        $result    = Validator::validator($this->validArray)
            ->whereOptionalType('nonexistent', 'string')
            ->passes();

        static::assertTrue($result);

        $validator = Validator::validator($this->validArray);
        $result    = $validator->whereOptionalType('age', 'int')
            ->passes();

        static::assertTrue($result);
    }

    public function testWhereNot(): void {
        $result = Validator::validator($this->validArray)
            ->whereNot('nonexistent')
            ->passes();

        static::assertTrue($result);
    }

    public function testWhereNotWithExistingKey(): void {
        $result = Validator::validator($this->validArray)
            ->whereNot('name')
            ->passes();

        static::assertFalse($result);
    }

    public function testWhereInWithValidValue(): void {
        $result = Validator::validator($this->validArray)
            ->whereIn('name', [
                'John',
                'Jane',
                'Alice',
            ], )
            ->passes();

        static::assertTrue($result);
    }

    public function testWhereInWithInvalidValue(): void {
        $result = Validator::validator($this->validArray)
            ->whereIn('name', [
                'Jane',
                'Alice',
            ], )
            ->passes();

        static::assertFalse($result);
    }

    public function testWhereInWithMissingKey(): void {
        $result = Validator::validator($this->validArray)
            ->whereIn('nonexistent', [
                'value1',
                'value2',
            ], )
            ->passes();

        static::assertFalse($result);
    }

    public function testWhereInWithEnumClass(): void {
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

        $result        = $validator->whereIn('name', $mockEnumClass)
            ->passes();

        static::assertTrue($result);
    }

    public function testHas(): void {
        $result = Validator::validator($this->validArray)
            ->has('name')
            ->passes();

        static::assertTrue($result);
    }

    public function testHasWithMissingKey(): void {
        $result = Validator::validator($this->validArray)
            ->has('nonexistent')
            ->passes();

        static::assertFalse($result);
    }

    public function testHasAll(): void {
        $result = Validator::validator($this->validArray)
            ->hasAll([
                'name',
                'age',
                'email',
            ], )
            ->passes();

        static::assertTrue($result);
    }

    public function testHasAllWithMissingKey(): void {
        $result = Validator::validator($this->validArray)
            ->hasAll([
                'name',
                'nonexistent',
            ], )
            ->passes();

        static::assertFalse($result);
    }

    public function testHasNot(): void {
        $result = Validator::validator($this->validArray)
            ->hasNot('nonexistent')
            ->passes();

        static::assertTrue($result);
    }

    public function testHasNotWithExistingKey(): void {
        $result = Validator::validator($this->validArray)
            ->hasNot('name')
            ->passes();

        static::assertFalse($result);
    }

    public function testHasNone(): void {
        $result = Validator::validator($this->validArray)
            ->hasNone([
                'nonexistent1',
                'nonexistent2',
            ], )
            ->passes();

        static::assertTrue($result);
    }

    public function testHasNoneWithExistingKey(): void {
        $result = Validator::validator($this->validArray)
            ->hasNone([
                'nonexistent',
                'name',
            ], )
            ->passes();

        static::assertFalse($result);
    }

    public function testHasAnyOf(): void {
        $result = Validator::validator($this->validArray)
            ->hasAnyOf('name', 'nonexistent')
            ->passes();

        static::assertTrue($result);
    }

    public function testHasAnyOfWithAllMissingKeys(): void {
        $result = Validator::validator($this->validArray)
            ->hasAnyOf('nonexistent1', 'nonexistent2')
            ->passes();

        static::assertFalse($result);
    }

    public function testWhereIsFile(): void {
        $tempFile = tempnam(sys_get_temp_dir(), 'test');

        $data     = [
            'file_path' => $tempFile,
        ];
        $result   = Validator::validator($data)
            ->whereIsFile('file_path')
            ->passes();

        static::assertTrue($result);

        unlink($tempFile);
    }

    public function testWhereIsFileWithNonexistentFile(): void {
        $data   = [
            'file_path' => '/path/to/nonexistent/file.txt',
        ];
        $result = Validator::validator($data)
            ->whereIsFile('file_path')
            ->passes();

        static::assertFalse($result);
    }

    public function testWhereIsFileWithNonStringValue(): void {
        $data   = [
            'file_path' => 123,
        ];
        $result = Validator::validator($data)
            ->whereIsFile('file_path')
            ->passes();

        static::assertFalse($result);
    }

    public function testWhereIsFileWithMissingKey(): void {
        $result = Validator::validator($this->validArray)
            ->whereIsFile('nonexistent')
            ->passes();

        static::assertFalse($result);
    }

    public function testWhereIsFileWithExistenceFlagOff(): void {
        $data   = [
            'file_path' => '/path/to/nonexistent/file.txt',
        ];
        $result = Validator::validator($data)
            ->whereIsFile('file_path', false)
            ->passes();

        static::assertTrue($result);
    }

    public function testWhereIsValid(): void {
        $result = Validator::validator($this->validArray)
            ->whereIsValid('email', function ($email) {
                return false !== filter_var($email, FILTER_VALIDATE_EMAIL) ? true : 'Invalid email format';
            }, )
            ->passes();

        static::assertTrue($result);
    }

    public function testWhereIsValidWithInvalidValue(): void {
        $data   = [
            'email' => 'not-an-email',
        ];
        $result = Validator::validator($data)
            ->whereIsValid('email', function ($email) {
                return false !== filter_var($email, FILTER_VALIDATE_EMAIL) ? true : 'Invalid email format';
            }, )
            ->passes();

        static::assertFalse($result);
    }

    public function testWhereIsValidWithMissingKey(): void {
        $result = Validator::validator($this->validArray)
            ->whereIsValid('nonexistent', function () {
                return true;
            }, )
            ->passes();

        static::assertFalse($result);
    }

    public function testWhereRegexMatch(): void {
        $result = Validator::validator($this->validArray)
            ->whereRegexMatch('email', '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/')
            ->passes();

        static::assertTrue($result);
    }

    public function testWhereRegexMatchWithInvalidValue(): void {
        $data   = [
            'email' => 'not-an-email',
        ];
        $result = Validator::validator($data)
            ->whereRegexMatch('email', '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/')
            ->passes();

        static::assertFalse($result);
    }

    public function testWhereRegexMatchWithMatchAll(): void {
        $data   = [
            'text' => 'one two three',
        ];
        $result = Validator::validator($data)
            ->whereRegexMatch('text', '/\w+/', true)
            ->passes();

        static::assertTrue($result);
    }

    public function testWhereRegexMatchWithMissingKey(): void {
        $result = Validator::validator($this->validArray)
            ->whereRegexMatch('nonexistent', '/pattern/')
            ->passes();

        static::assertFalse($result);
    }

    public function testWhereRegexMatchWithNonStringValue(): void {
        $result = Validator::validator($this->validArray)
            ->whereRegexMatch('age', '/pattern/')
            ->passes();

        static::assertFalse($result);
    }

    public function testPasses(): void {
        $result = Validator::validator($this->validArray)
            ->has('name')
            ->passes();

        static::assertTrue($result);
    }

    public function testFails(): void {
        $result = Validator::validator($this->validArray)
            ->has('nonexistent')
            ->fails();

        static::assertTrue($result);
    }

    public function testErrors(): void {
        $validator = Validator::validator($this->validArray)
            ->has('nonexistent')
            ->where('name', 'Jane');

        $validator->passes();
        $errors    = $validator->errors();

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

    public function testValidate(): void {
        $validator = Validator::validator($this->validArray)
            ->has('name');

        $data      = $validator->validate();

        static::assertEquals($this->validArray, $data);
    }

    public function testValidateWithFailedValidationThrowsException(): void {
        $this->expectException(JsonValidationException::class);

        $validator = Validator::validator($this->validArray)
            ->has('nonexistent');

        $validator->validate();
    }

    public function testChainedValidation(): void {
        $result = Validator::validator($this->validArray)
            ->has('name')
            ->where('name', 'John')
            ->whereType('age', 'integer')
            ->whereIs('age', fn ($age) => $age >= 18)
            ->whereIn('email', [
                'john@example.com',
                'jane@example.com',
            ], )
            ->whereIsValid('email', fn ($email) => false !== filter_var($email, FILTER_VALIDATE_EMAIL))
            ->whereRegexMatch('email', '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/')
            ->hasAll([
                'name',
                'age',
                'email',
            ], )
            ->hasNone([
                'nonexistent1',
                'nonexistent2',
            ], )
            ->hasAnyOf('name', 'nonexistent')
            ->passes();

        static::assertTrue($result);
    }

    public function testWhereInWithPureEnum(): void {
        $data                   = $this->testData;
        $data['user']['status'] = UserStatus::Active->name;

        $validator              = Validator::validator($data);
        $result                 = $validator->whereIn('user.status', UserStatus::class)
            ->passes();

        static::assertTrue($result);

        $data['user']['status'] = 'Unknown';

        $result                 = Validator::validator($data)
            ->whereIn('user.status', UserStatus::class)
            ->passes();

        static::assertFalse($result);
    }

    public function testWhereInWithBackedEnum(): void {
        $validator            = Validator::validator($this->testData);
        $result               = $validator->whereIn('user.role', UserRole::class)
            ->passes();

        static::assertTrue($result);

        $data                 = $this->testData;
        $data['user']['role'] = 'guest';

        $result               = Validator::validator($data)
            ->whereIn('user.role', UserRole::class)
            ->passes();

        static::assertFalse($result);
    }

    /**
     * @throws JsonException
     */
    public function testWhereTypeWithEnum(): void {
        $json      = '{"status":"active"}';
        $validator = Validator::validator($json);

        $result    = $validator->whereIsValid('status', function ($value) {
            return in_array($value, [
                'active',
                'inactive',
            ], true, ) ? true : 'Status must be either active or inactive';
        })
            ->passes();

        static::assertTrue($result);

        $json      = json_encode([
            'status' => 'pending',
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);

        $result    = $validator->whereIsValid('status', function ($value) {
            return in_array($value, [
                'active',
                'inactive',
            ], true, ) ? true : 'Status must be either active or inactive';
        })
            ->passes();

        static::assertFalse($result);
        $errors    = $validator->errors();
        static::assertArrayHasKey('status', $errors);
        static::assertContains('Status must be either active or inactive', $errors['status']);
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

        $result    = $validator->whereType('user.id', 'integer')
            ->whereType('user.name', 'string')
            ->whereIn('user.status', UserStatus::class)
            ->whereIn('user.role', UserRole::class)
            ->whereType('user.verified', 'boolean')
            ->passes();

        static::assertTrue($result);
    }

    public function testHasWithDotNotation(): void {
        $validator = Validator::validator($this->nestedData);
        $result    = $validator->has('user.profile.name')
            ->passes();

        static::assertTrue($result);
    }

    public function testHasWithDeepNestedDotNotation(): void {
        $validator = Validator::validator($this->nestedData);
        $result    = $validator->has('user.profile.contact.address.country')
            ->passes();

        static::assertTrue($result);
    }

    public function testHasWithNonExistentNestedKey(): void {
        $validator = Validator::validator($this->nestedData);
        $result    = $validator->has('user.profile.contact.address.zipcode')
            ->passes();

        static::assertFalse($result);
    }

    public function testWhereWithDotNotation(): void {
        $validator = Validator::validator($this->nestedData);
        $result    = $validator->where('user.profile.name', 'John Doe')
            ->passes();

        static::assertTrue($result);
    }

    public function testWhereTypeWithDotNotation(): void {
        $validator = Validator::validator($this->nestedData);
        $result    = $validator->whereType('user.id', 'integer')
            ->whereType('user.profile.name', 'string')
            ->whereType('user.settings.notifications', 'boolean')
            ->whereType('metadata.version', 'float')
            ->passes();

        static::assertTrue($result);
    }

    public function testWhereInWithDotNotation(): void {
        $validator = Validator::validator($this->nestedData);
        $result    = $validator->whereIn('user.settings.theme', [
            'light',
            'dark',
            'auto',
        ], )
            ->passes();

        static::assertTrue($result);
    }

    public function testWhereIsWithDotNotation(): void {
        $validator = Validator::validator($this->nestedData);
        $result    = $validator->whereIs('user.profile.name', function ($value) {
            return str_starts_with($value, 'John');
        }, )
            ->passes();

        static::assertTrue($result);
    }

    public function testWhereRegexMatchWithDotNotation(): void {
        $validator = Validator::validator($this->nestedData);
        $result    = $validator->whereRegexMatch('user.profile.email', '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/')
            ->passes();

        static::assertTrue($result);
    }

    public function testWhereNotWithDotNotation(): void {
        $validator = Validator::validator($this->nestedData);
        $result    = $validator->whereNot('user.profile.contact.address.zipcode')
            ->passes();

        static::assertTrue($result);
    }

    public function testHasAllWithDotNotation(): void {
        $validator = Validator::validator($this->nestedData);
        $result    = $validator->hasAll([
            'user.profile.name',
            'user.profile.email',
            'user.settings.theme',
        ])
            ->passes();

        static::assertTrue($result);
    }

    public function testHasAnyOfWithDotNotation(): void {
        $validator = Validator::validator($this->nestedData);
        $result    = $validator->hasAnyOf('user.profile.nonexistent', 'user.settings.theme', 'user.unknown')
            ->passes();

        static::assertTrue($result);
    }

    public function testWhereIsValidWithDotNotation(): void {
        $validator = Validator::validator($this->nestedData);
        $result    = $validator->whereIsValid('user.profile.email', function ($email) {
            return false !== filter_var($email, FILTER_VALIDATE_EMAIL) ? true : 'Invalid email format';
        }, )
            ->passes();

        static::assertTrue($result);
    }

    public function testComplexValidationWithDotNotation(): void {
        $validator = Validator::validator($this->nestedData);
        $result    = $validator->has('user.profile')
            ->whereType('user.profile', 'array')
            ->whereType('user.id', 'integer')
            ->where('user.profile.contact.address.country', 'USA')
            ->whereRegexMatch('user.profile.contact.phone', '/^\d{3}-\d{3}-\d{4}$/')
            ->whereIn('user.settings.theme', [
                'light',
                'dark',
            ], )
            ->whereIs('metadata.created_at', function ($date) {
                return false !== strtotime($date);
            }, )
            ->passes();

        static::assertTrue($result);
    }

    public function testValidationWithArrayElements(): void {
        $validator = Validator::validator($this->nestedData);
        $result    = $validator->has('posts.0.title')
            ->where('posts.1.title', 'Second Post')
            ->passes();

        static::assertTrue($result);
    }

    public function testNestedPartialValidation(): void {
        $userProfile = $this->nestedData['user']['profile'];

        $validator   = Validator::validator([
            'profile' => $userProfile,
        ]);
        $result      = $validator->has('profile.name')
            ->has('profile.contact.address.city')
            ->where('profile.contact.address.country', 'USA')
            ->passes();

        static::assertTrue($result);
    }

    /**
     * @throws JsonException
     */
    public function testWhereContainsType(): void {
        $json      = json_encode([
            'array' => [
                1,
                2,
                3,
            ],
        ], flags: JSON_THROW_ON_ERROR, );
        $validator = Validator::validator($json);
        static::assertTrue($validator->whereContainsType('array', 'integer')
            ->passes(), );

        $json      = json_encode([
            'array' => [
                1,
                'string',
                3,
            ],
        ], flags: JSON_THROW_ON_ERROR, );
        $validator = Validator::validator($json);
        static::assertFalse($validator->whereContainsType('array', 'integer')
            ->passes(), );

        $json      = json_encode([
            'array' => 'string',
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertFalse($validator->whereContainsType('array', 'integer')
            ->passes(), );

        $json      = json_encode([
            'a' => [
                'b' => [
                    'array' => 'string',
                ],
            ],
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        $result    = $validator->whereContainsType('a.b', 'string')
            ->passes();
        static::assertTrue($result);

        $json      = json_encode([
            'a' => [
                'b' => [
                    'array' => 'string',
                ],
            ],
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        $result    = $validator->whereContainsType('a.b', 'int')
            ->passes();
        static::assertFalse($result);
    }

    /**
     * @throws JsonException
     */
    public function testWhereDate(): void {
        $json      = json_encode([
            'date' => '2023-05-15',
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertTrue($validator->whereDate('date')
            ->passes(), );

        $json      = json_encode([
            'date' => '15/05/2023',
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertTrue($validator->whereDate('date', 'd/m/Y')
            ->passes(), );

        $json      = json_encode([
            'date' => 'not-a-date',
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertFalse($validator->whereDate('date')
            ->passes(), );
        static::assertArrayHasKey('date', $validator->errors());
    }

    /**
     * @throws JsonException
     */
    public function testWhereEmail(): void {
        $json      = json_encode([
            'email' => 'test@example.com',
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertTrue($validator->whereEmail('email')
            ->passes(), );

        $json      = json_encode([
            'email' => 'not-an-email',
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertFalse($validator->whereEmail('email')
            ->passes(), );
        static::assertArrayHasKey('email', $validator->errors());
    }

    /**
     * @throws JsonException
     */
    public function testWhereUrl(): void {
        $json      = json_encode([
            'url' => 'https://example.com',
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertTrue($validator->whereUrl('url')
            ->passes(), );

        $json      = json_encode([
            'url' => 'not-a-url',
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertFalse($validator->whereUrl('url')
            ->passes(), );
        static::assertArrayHasKey('url', $validator->errors());
    }

    /**
     * @throws JsonException
     */
    public function testWhereIp(): void {
        $json      = json_encode([
            'ip' => '192.168.1.1',
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertTrue($validator->whereIp('ip')
            ->passes(), );

        $json      = json_encode([
            'ip' => '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertTrue($validator->whereIp('ip')
            ->passes(), );

        $json      = json_encode([
            'ip' => 'not-an-ip',
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertFalse($validator->whereIp('ip')
            ->passes(), );
        static::assertArrayHasKey('ip', $validator->errors());

        $json      = json_encode([
            'ip' => '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertFalse($validator->whereIp('ip', FILTER_FLAG_IPV4)
            ->passes(), );
    }

    /**
     * @throws JsonException
     */
    public function testWhereBetween(): void {
        $json      = json_encode([
            'number' => 50,
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertTrue($validator->whereBetween('number', 1, 100)
            ->passes(), );

        $json      = json_encode([
            'number' => 1,
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertTrue($validator->whereBetween('number', 1, 100)
            ->passes(), );

        $json      = json_encode([
            'number' => 101,
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertFalse($validator->whereBetween('number', 1, 100)
            ->passes(), );
        static::assertArrayHasKey('number', $validator->errors());

        $json      = json_encode([
            'number' => 'string',
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertFalse($validator->whereBetween('number', 1, 100)
            ->passes(), );
    }

    /**
     * @throws JsonException
     */
    public function testWhereLength(): void {
        $json      = json_encode([
            'string' => 'hello',
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertTrue($validator->whereLength('string', 5)
            ->passes(), );

        $validator = Validator::validator($json);
        static::assertTrue($validator->whereLength('string', null, 3, 10)
            ->passes(), );

        $validator = Validator::validator($json);
        static::assertFalse($validator->whereLength('string', null, 10)
            ->passes(), );

        $validator = Validator::validator($json);
        static::assertFalse($validator->whereLength('string', null, null, 3)
            ->passes(), );

        $json      = json_encode([
            'array' => [
                1,
                2,
                3,
            ],
        ], flags: JSON_THROW_ON_ERROR, );
        $validator = Validator::validator($json);
        static::assertTrue($validator->whereLength('array', 3)
            ->passes(), );

        $json      = json_encode([
            'value' => 123,
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertFalse($validator->whereLength('value')
            ->passes(), );
    }

    /**
     * @throws JsonException
     */
    public function testWhereContains(): void {
        $json      = json_encode([
            'string' => 'hello world',
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertTrue($validator->whereContains('string', 'world')
            ->passes(), );

        $validator = Validator::validator($json);
        static::assertTrue($validator->whereContains('string', 'WORLD', false)
            ->passes(), );

        $validator = Validator::validator($json);
        static::assertFalse($validator->whereContains('string', 'missing')
            ->passes(), );

        $json      = json_encode([
            'string' => 123,
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertFalse($validator->whereContains('string', 'world')
            ->passes(), );
    }

    /**
     * @throws JsonException
     */
    public function testWhereEach(): void {
        $json      = json_encode([
            'array' => [
                1,
                2,
                3,
            ],
        ], flags: JSON_THROW_ON_ERROR, );
        $validator = Validator::validator($json);
        $result    = $validator->whereEach('array', function ($item) {
            return $item > 0 ? true : 'Must be positive';
        })
            ->passes();
        static::assertTrue($result);

        $json      = json_encode([
            'array' => [
                1,
                -2,
                3,
            ],
        ], flags: JSON_THROW_ON_ERROR, );
        $validator = Validator::validator($json);
        $result    = $validator->whereEach('array', function ($item) {
            return $item > 0 ? true : 'Must be positive';
        })
            ->passes();
        static::assertFalse($result);
        static::assertArrayHasKey('array.1', $validator->errors());

        $json      = json_encode([
            'array' => 'string',
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        $result    = $validator->whereEach('array', function () {
            return true;
        })
            ->passes();
        static::assertFalse($result);
    }

    /**
     * @throws JsonException
     */
    public function testWhereNotEmpty(): void {
        $json      = json_encode([
            'string' => 'hello',
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertTrue($validator->whereNotEmpty('string')
            ->passes(), );

        $json      = json_encode([
            'string' => '',
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertFalse($validator->whereNotEmpty('string')
            ->passes(), );

        $json      = json_encode([
            'array' => [
                1,
                2,
                3,
            ],
        ], flags: JSON_THROW_ON_ERROR, );
        $validator = Validator::validator($json);
        static::assertTrue($validator->whereNotEmpty('array')
            ->passes(), );

        $json      = json_encode([
            'array' => [],
        ], flags: JSON_THROW_ON_ERROR);
        $validator = Validator::validator($json);
        static::assertFalse($validator->whereNotEmpty('array')
            ->passes(), );
    }

    /**
     * @throws JsonException
     */
    public function testWhereSchema(): void {
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
        static::assertTrue($validator->whereSchema('', $schema)
            ->passes(), );

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
        static::assertTrue($validator->whereSchema('', $complexSchema)
            ->passes(), );

        $schemaWithCallback = [
            'name'  => 'string',
            'email' => function ($validator, $key) {
                $validator->whereEmail($key);
            },
        ];

        $validator          = Validator::validator($jsonData);
        static::assertTrue($validator->whereSchema('', $schemaWithCallback)
            ->passes(), );

        $jsonData           = json_encode([
            'name'  => 123,
            'email' => 'not-an-email',
            'age'   => 'thirty',
        ], flags: JSON_THROW_ON_ERROR);

        $validator          = Validator::validator($jsonData);
        static::assertFalse($validator->whereSchema('', $schema)
            ->passes(), );
        static::assertCount(3, $validator->errors());
    }
}

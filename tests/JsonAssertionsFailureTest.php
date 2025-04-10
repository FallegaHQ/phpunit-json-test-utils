<?php
declare(strict_types=1);

namespace FallegaHQ\PhpunitJsonTestUtils\Tests;

use FallegaHQ\PhpunitJsonTestUtils\JsonAssertions;
use FallegaHQ\PhpunitJsonTestUtils\JsonValidationException;
use FallegaHQ\PhpunitJsonTestUtils\JsonValidator;
use FallegaHQ\PhpunitJsonTestUtils\JsonValidatorAssertion;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

/**
 * This class demonstrates expected failure scenarios with JsonAssertions
 * Note: When running these tests, they will fail as intended
 */
#[CoversClass(JsonAssertions::class)]
#[CoversClass(JsonValidator::class)]
#[CoversClass(JsonValidatorAssertion::class)]
#[CoversClass(JsonValidationException::class)]
class JsonAssertionsFailureTest extends TestCase{
    use JsonAssertions;

    public function testWithCustomErrorMessage(): void{
        $data = ['status' => 'success'];

        $this->assertValidJson($data)
             ->hasKey('status')
             ->assert('The JSON should have a status key');

        try{
            $this->assertValidJson($data)
                 ->hasKey('missing_key')
                 ->assert('The JSON must contain the missing_key property');
        }
        catch(ExpectationFailedException){
            $this->addToAssertionCount(1);
        }
    }

    public function testFailingJsonValidation(): void{
        $data = $this->getInvalidTestData();

        try{
            $this->assertValidJson($data)
                 ->isType('id', 'int')
                 ->notEmpty('name')
                 ->isEmail('email')
                 ->isType('active', 'bool')
                 ->notEmpty('tags')
                 ->hasKey('created_at')
                 ->isUrl('profile.website')
                 ->passes('profile.age', function($value){
                     return $value >= 0;
                 },       'Age must be a positive number')
                 ->assert();
        }
        catch(ExpectationFailedException){
            $this->addToAssertionCount(6);
        }
    }

    /**
     * @return array
     */
    private function getInvalidTestData(): array{
        return [
            'id'      => 'abc',
            'name'    => '',
            'email'   => 'not-a-valid-email',
            'active'  => 'yes',
            'tags'    => [],
            'profile' => [
                'age'     => -5,
                'website' => 'not-a-url',
            ],
        ];
    }

    public function testIndividualFailingAssertions(): void{
        $data = $this->getInvalidTestData();

        try{
            $this->assertJsonType($data, 'id', 'int');
        }
        catch(ExpectationFailedException $e){
            self::assertSame($e->getMessage(), "The 'id' must be of type: int\nFailed asserting that false is true.");
        }
    }

    public function testInvalidJsonString(): void{
        $invalidJson = '{name: "Missing quotes", broken json}';
        try{
            $this->assertValidJson($invalidJson)
                 ->hasKey('name');
        }
        catch(JsonValidationException $e){
            self::assertSame($e->getMessage(), 'Invalid JSON string: Syntax error');
            $this->addToAssertionCount(1);
        }
    }

    public function testWithApiResponseExample(): void{
        $apiResponse = json_encode([
                                       'data' => [
                                           'users'      => [
                                               [
                                                   'id'    => 1,
                                                   'name'  => 'John Doe',
                                                   'email' => 'john@example.com',
                                               ],
                                               [
                                                   'id'    => 2,
                                                   'name'  => 'Jane Smith',
                                                   'email' => 'jane@example.com',
                                               ],
                                           ],
                                           'pagination' => [
                                               'current_page' => 1,
                                               'total_pages'  => 5,
                                           ],
                                       ],
                                       'meta' => [
                                           'status'  => 'success',
                                           'version' => '1.0',
                                       ],
                                   ]);

        $this->assertValidJson($apiResponse)
             ->hasKey('data.users')
             ->hasKey('data.pagination')
             ->isType('data.users', 'array')
             ->equals('meta.status', 'success')
             ->isType('data.pagination.current_page', 'int')
             ->assert();
    }
}

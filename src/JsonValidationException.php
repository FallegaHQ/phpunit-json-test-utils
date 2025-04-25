<?php

/**
 * Made with love.
 */
declare(strict_types = 1);
namespace FallegaHQ\JsonTestUtils;

use InvalidArgumentException;
use Throwable;

class JsonValidationException extends InvalidArgumentException {
    protected array $errors = [];

    public function __construct(string $message = '', array $errors = [], int $code = 0, ?Throwable $previous = null) {
        $this->errors = $errors;
        parent::__construct($message, $code, $previous);
    }

    public function getErrors(): array {
        return $this->errors;

    }
}

<?php

declare(strict_types=1);

namespace SpoutX\Reader\Exception;

use Throwable;

/**
 * Class InvalidValueException
 */
class InvalidValueException extends ReaderException
{
    private mixed $invalidValue;

    public function __construct(mixed $invalidValue, string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        $this->invalidValue = $invalidValue;
        parent::__construct($message, $code, $previous);
    }

    public function getInvalidValue(): mixed
    {
        return $this->invalidValue;
    }
}

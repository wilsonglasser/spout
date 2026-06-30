<?php

declare(strict_types=1);

namespace SpoutX\Common\Helper;

/**
 * Class CellTypeHelper
 * This class provides helper functions to determine the type of the cell value
 */
class CellTypeHelper
{
    /**
     * @param $value
     * @return bool Whether the given value is considered "empty"
     */
    public static function isEmpty(mixed $value): bool
    {
        return ($value === null || $value === '');
    }

    /**
     * @param $value
     * @return bool Whether the given value is a non empty string
     */
    public static function isNonEmptyString(mixed $value): bool
    {
        return (gettype($value) === 'string' && $value !== '');
    }

    /**
     * Returns whether the given value is numeric.
     * A numeric value is from type "integer" or "double" ("float" is not returned by gettype).
     *
     * @param $value
     * @return bool Whether the given value is numeric
     */
    public static function isNumeric(mixed $value): bool
    {
        $valueType = gettype($value);

        return ($valueType === 'integer' || $valueType === 'double');
    }

    /**
     * Returns whether the given value is boolean.
     * "true"/"false" and 0/1 are not booleans.
     *
     * @param $value
     * @return bool Whether the given value is boolean
     */
    public static function isBoolean(mixed $value): bool
    {
        return gettype($value) === 'boolean';
    }

    /**
     * Returns whether the given value is a DateTime or DateInterval object.
     *
     * @param $value
     * @return bool Whether the given value is a DateTime or DateInterval object
     */
    public static function isDateTimeOrDateInterval(mixed $value): bool
    {
        return (
            $value instanceof \DateTime ||
            $value instanceof \DateInterval
        );
    }

}

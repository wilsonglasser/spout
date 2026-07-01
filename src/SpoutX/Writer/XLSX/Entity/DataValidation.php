<?php

declare(strict_types=1);

namespace SpoutX\Writer\XLSX\Entity;

use SpoutX\Common\Exception\InvalidArgumentException;

/**
 * A single <dataValidation> applied to a cell range (sqref).
 *
 * For the common dropdown-list case use the {@see DataValidation::listFromValues()}
 * or {@see DataValidation::listFromRange()} factories. For numeric/date/textLength
 * constraints build directly with a type + operator + formula(s).
 *
 * formula1/formula2 are stored ready to be written verbatim inside
 * <formula1>/<formula2>; the list factories take care of quoting/escaping.
 */
class DataValidation
{
    public function __construct(
        public string $sqref,
        public ValidationType $type,
        public string $formula1,
        public ?string $formula2 = null,
        public ?ValidationOperator $operator = null,
        public bool $allowBlank = true,
        public bool $showInputMessage = true,
        public bool $showErrorMessage = true,
        public ValidationErrorStyle $errorStyle = ValidationErrorStyle::Stop,
        public ?string $promptTitle = null,
        public ?string $prompt = null,
        public ?string $errorTitle = null,
        public ?string $error = null,
    ) {
    }

    /**
     * A dropdown list from explicit string values.
     * Values must not contain a comma (the OOXML list delimiter).
     *
     * @param string[] $values
     */
    public static function listFromValues(string $sqref, array $values, bool $allowBlank = true): self
    {
        foreach ($values as $value) {
            if (str_contains($value, ',')) {
                throw new InvalidArgumentException(
                    "List validation value '$value' contains a comma, which is used as the delimiter and is not allowed."
                );
            }
        }

        $formula1 = '"' . implode(',', array_map(
            static fn (string $option): string => htmlspecialchars($option, ENT_XML1),
            $values
        )) . '"';

        return new self($sqref, ValidationType::List, $formula1, allowBlank: $allowBlank);
    }

    /**
     * A dropdown list backed by a cell range, e.g. "$A$1:$A$10" or "Lists!$A$1:$A$10".
     */
    public static function listFromRange(string $sqref, string $range, bool $allowBlank = true): self
    {
        return new self($sqref, ValidationType::List, htmlspecialchars($range, ENT_XML1), allowBlank: $allowBlank);
    }
}

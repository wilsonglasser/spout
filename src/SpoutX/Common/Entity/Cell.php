<?php

declare(strict_types=1);

namespace SpoutX\Common\Entity;

use SpoutX\Common\Entity\Style\Style;
use SpoutX\Common\Helper\CellTypeHelper;

/**
 * A single cell. Mutable by design: build it then mutate value/style/type as needed.
 */
class Cell
{
    /** The formula of this cell (without the leading "="), when it holds a formula. */
    protected ?string $formula = null;

    /** The value of this cell. */
    protected mixed $value = null;

    /** The cell type. */
    protected CellType $type = CellType::Empty;

    /** The cell style. */
    protected Style $style;

    public function __construct(mixed $value, ?Style $style = null)
    {
        $this->setValue($value);
        $this->setStyle($style);
    }

    public function setValue(mixed $value): void
    {
        $this->type = self::detectType($value);

        if ($this->isFormula()) {
            $this->formula = ltrim((string) $value, '=');
            $this->value = '';
        } else {
            $this->value = $value;
        }
    }

    /**
     * Overrides the displayed value for a formula cell (SpoutX does not compute formulas).
     */
    public function setCalculatedValue(mixed $value): void
    {
        $this->value = $value;
    }

    public function getValue(): mixed
    {
        return !$this->isError() ? $this->value : null;
    }

    public function getFormula(): ?string
    {
        return !$this->isError() ? $this->formula : null;
    }

    public function setStyle(?Style $style): void
    {
        $this->style = $style ?? new Style();
    }

    public function getStyle(): Style
    {
        return $this->style;
    }

    public function getType(): CellType
    {
        return $this->type;
    }

    public function setType(CellType $type): void
    {
        $this->type = $type;
    }

    /**
     * Detects the type of a value.
     */
    public static function detectType(mixed $value): CellType
    {
        if ($value instanceof RichText) {
            return CellType::RichText;
        }
        if (CellTypeHelper::isBoolean($value)) {
            return CellType::Boolean;
        }
        if (is_string($value) && str_starts_with($value, '=')) {
            return CellType::Formula;
        }
        if (CellTypeHelper::isEmpty($value)) {
            return CellType::Empty;
        }
        if (CellTypeHelper::isNumeric($value)) {
            return CellType::Numeric;
        }
        if (CellTypeHelper::isDateTimeOrDateInterval($value)) {
            return CellType::Date;
        }
        if (CellTypeHelper::isNonEmptyString($value)) {
            return CellType::String;
        }

        return CellType::Error;
    }

    public function isBoolean(): bool
    {
        return $this->type === CellType::Boolean;
    }

    public function isFormula(): bool
    {
        return $this->type === CellType::Formula;
    }

    public function isEmpty(): bool
    {
        return $this->type === CellType::Empty;
    }

    public function isNumeric(): bool
    {
        return $this->type === CellType::Numeric;
    }

    public function isString(): bool
    {
        return $this->type === CellType::String;
    }

    /** Explicit text (never coerced to a number by the writer). */
    public function isText(): bool
    {
        return $this->type === CellType::Text;
    }

    public function isDate(): bool
    {
        return $this->type === CellType::Date;
    }

    public function isError(): bool
    {
        return $this->type === CellType::Error;
    }

    public function isRichText(): bool
    {
        return $this->type === CellType::RichText;
    }

    public function __toString(): string
    {
        return (string) $this->getValue();
    }
}

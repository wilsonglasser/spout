<?php

declare(strict_types=1);

namespace SpoutX\Common\Entity;

use SpoutX\Common\Entity\Style\Style;
use SpoutX\Writer\Common\Helper\CellHelper;

class ColumnDimension
{
    public const DEFAULT_COLUMN_WIDTH = 9.1;

    /** Outline level */
    private int $outlineLevel = 0;

    /** Collapsed */
    private bool $collapsed = false;

    /**
     * @param string|int $columnIndex Character column index
     * @param float      $width       Column width. A negative value means the width should be ignored by the writer.
     * @param bool       $autoSize    Whether the column width should be calculated from its content
     * @param bool       $visible     Whether the column is visible
     */
    public function __construct(
        private string|int $columnIndex = 'A',
        private float $width = -1,
        private bool $autoSize = false,
        private bool $visible = true,
    ) {
    }

    /**
     * @return string|int
     */
    public function getColumnIndex(): string|int
    {
        return $this->columnIndex;
    }

    public function setColumnIndex(string|int $value): self
    {
        $this->columnIndex = $value;

        return $this;
    }

    public function getWidth(): float
    {
        return $this->width;
    }

    public function setWidth(float $value = -1): self
    {
        $this->width = $value;

        return $this;
    }

    public function getAutoSize(): bool
    {
        return $this->autoSize;
    }

    public function setAutoSize(bool $value = false): self
    {
        $this->autoSize = $value;

        return $this;
    }

    public function getVisible(): bool
    {
        return $this->visible;
    }

    public function setVisible(bool $value = true): self
    {
        $this->visible = $value;

        return $this;
    }

    public function getOutlineLevel(): int
    {
        return $this->outlineLevel;
    }

    /**
     * Value must be between 0 and 7.
     *
     * @throws \InvalidArgumentException
     */
    public function setOutlineLevel(int $value): self
    {
        if ($value < 0 || $value > 7) {
            throw new \InvalidArgumentException('Outline level must range between 0 and 7.');
        }

        $this->outlineLevel = $value;

        return $this;
    }

    public function getCollapsed(): bool
    {
        return $this->collapsed;
    }

    public function setCollapsed(bool $value = true): self
    {
        $this->collapsed = $value;

        return $this;
    }

    /**
     * Approximate width in pixels for a string of text in a given font.
     *
     * @return int Text width in pixels (no padding added)
     */
    public static function getTextWidthPixelsApprox(int $cellLength, ?Style $style = null): int
    {
        $fontName = $style->getFontName();
        $fontSize = $style->getFontSize();

        // Calculate column width in pixels. We assume fixed glyph width. Result varies with font name and size.
        switch ($fontName) {
            case 'Arial':
                // value 7 was found via interpolation by inspecting real Excel files with Arial 10 font.
                // value 8 was set because of experience in different exports at Arial 10 font.
                $byWidth = 8;
                $bySize = 10;
                break;

            case 'Verdana':
                $byWidth = 8;
                $bySize = 10;
                break;

            case 'Calibri':
            default:
                // value 8.26 was found via interpolation by inspecting real Excel files with Calibri 11 font.
                $byWidth = 8.26;
                $bySize = 11;
                break;
        }

        $columnWidth = (int) ($byWidth * $cellLength);
        $columnWidth = $columnWidth * $fontSize / $bySize; // extrapolate from font size

        // pixel width is an integer
        return (int) $columnWidth;
    }

    /**
     * Calculate an (approximate) OpenXML column width, based on font size and text contained.
     *
     * @return float Column width
     */
    public static function calculateColumnWidth(int $cellLength, Style $defaultStyle): float
    {
        $columnWidthAdjust = self::getTextWidthPixelsApprox(1, $defaultStyle);
        // Width of text in pixels excl. padding, approximation
        // and addition because Excel adds some padding, just use approx width of 'n' glyph
        $columnWidth = self::getTextWidthPixelsApprox($cellLength, $defaultStyle) + $columnWidthAdjust * 3;

        // Convert from pixel width to column width
        $columnWidth = CellHelper::pixelsToCellDimension($columnWidth, $defaultStyle);

        return round($columnWidth, 6);
    }
}

<?php

namespace WilsonGlasser\Spout\Common\Entity;

use WilsonGlasser\Spout\Common\Entity\Style\Style;
use WilsonGlasser\Spout\Writer\Common\Helper\CellHelper;

class ColumnDimension
{

    const DEFAULT_COLUMN_WIDTH = 9.1;
    /**
     * Visible?
     *
     * @var bool
     */
    private $visible = true;

    /**
     * Outline level
     *
     * @var int
     */
    private $outlineLevel = 0;

    /**
     * Collapsed
     *
     * @var bool
     */
    private $collapsed = false;

    /**
     * Column index
     *
     * @var int
     */
    private $columnIndex;

    /**
     * Column width
     *
     * When this is set to a negative value, the column width should be ignored by IWriter
     *
     * @var double
     */
    private $width = -1;

    /**
     * Auto size?
     *
     * @var bool
     */
    private $autoSize = false;

    /**
     * Create a new ColumnDimension
     *
     * @param string|int $pIndex Character column index
     * @param float $width Column width
     * @param bool $autoSize Set to calculate the column width
     * @param bool $visible Is visible?
     */
    public function __construct($pIndex = 'A', $width=-1, $autoSize = false, $visible= true)
    {
        // Initialise values
        $this->columnIndex = $pIndex;
        $this->width = $width;
        $this->autoSize = $autoSize;
        $this->visible = $visible;
    }

    /**
     * Get ColumnIndex
     *
     * @return string
     */
    public function getColumnIndex()
    {
        return $this->columnIndex;
    }

    /**
     * Set ColumnIndex
     *
     * @param string $pValue
     * @return ColumnDimension
     */
    public function setColumnIndex($pValue)
    {
        $this->columnIndex = $pValue;
        return $this;
    }

    /**
     * Get Width
     *
     * @return double
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set Width
     *
     * @param double $pValue
     * @return ColumnDimension
     */
    public function setWidth($pValue = -1)
    {
        $this->width = $pValue;
        return $this;
    }

    /**
     * Get Auto Size
     *
     * @return bool
     */
    public function getAutoSize()
    {
        return $this->autoSize;
    }

    /**
     * Set Auto Size
     *
     * @param bool $pValue
     * @return ColumnDimension
     */
    public function setAutoSize($pValue = false)
    {
        $this->autoSize = $pValue;
        return $this;
    }

    /**
     * Get Visible
     *
     * @return bool
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * Set Visible
     *
     * @param bool $pValue
     * @return ColumnDimension
     */
    public function setVisible($pValue = true)
    {
        $this->visible = $pValue;
        return $this;
    }

    /**
     * Get Outline Level
     *
     * @return int
     */
    public function getOutlineLevel()
    {
        return $this->outlineLevel;
    }

    /**
     * Set Outline Level
     *
     * Value must be between 0 and 7
     *
     * @param int $pValue
     * @return ColumnDimension
     * @throws
     */
    public function setOutlineLevel($pValue)
    {
        if ($pValue < 0 || $pValue > 7) {
            throw new \Exception("Outline level must range between 0 and 7.");
        }

        $this->outlineLevel = $pValue;
        return $this;
    }

    /**
     * Get Collapsed
     *
     * @return bool
     */
    public function getCollapsed()
    {
        return $this->collapsed;
    }

    /**
     * Set Collapsed
     *
     * @param bool $pValue
     * @return ColumnDimension
     */
    public function setCollapsed($pValue = true)
    {
        $this->collapsed = $pValue;
        return $this;
    }

    /**
     * Get approximate width in pixels for a string of text in a certain font at a certain rotation angle
     *
     * @param int $cellLength
     * @param Style $style
     * @return int Text width in pixels (no padding added)
     */
    public static function getTextWidthPixelsApprox($cellLength, ?Style $style = null)
    {
        $fontName = $style->getFontName();
        $fontSize = $style->getFontSize();

        // Calculate column width in pixels. We assume fixed glyph width. Result varies with font name and size.
        switch ($fontName) {
            case 'Calibri':
            default:
                // value 8.26 was found via interpolation by inspecting real Excel files with Calibri 11 font.
                $byWidth = 8.26;
                $bySize = 11;
                break;

            case 'Arial':
                // value 7 was found via interpolation by inspecting real Excel files with Arial 10 font.
//                $columnWidth = (int) (7 * PHPExcel_Shared_String::CountCharacters($columnText));
                // value 8 was set because of experience in different exports at Arial 10 font.
                $byWidth = 8;
                $bySize = 10;
                break;

            case 'Verdana':
                $byWidth = 8;
                $bySize = 10;

                break;
        }

        $columnWidth = (int)($byWidth * $cellLength);
        $columnWidth = $columnWidth * $fontSize / $bySize; // extrapolate from font size

        // pixel width is an integer
        return (int)$columnWidth;
    }

    /**
     * Calculate an (approximate) OpenXML column width, based on font size and text contained
     *
     * @param int $cellLength
     * @param Style $defaultStyle Style object
     * @return integer Column width
     */
    public static function calculateColumnWidth($cellLength,  Style $defaultStyle)
    {
        $columnWidthAdjust = self::getTextWidthPixelsApprox(1, $defaultStyle);
        // Width of text in pixels excl. padding, approximation
        // and addition because Excel adds some padding, just use approx width of 'n' glyph
        $columnWidth = self::getTextWidthPixelsApprox($cellLength, $defaultStyle) + $columnWidthAdjust * 3;

        // Convert from pixel width to column width
        $columnWidth = CellHelper::pixelsToCellDimension($columnWidth, $defaultStyle);

        // Return
        return round($columnWidth, 6);
    }
}

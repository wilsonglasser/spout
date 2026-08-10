<?php

declare(strict_types=1);

namespace SpoutX\Writer\Common\Creator\Style;

use SpoutX\Common\Entity\Style\Border;
use SpoutX\Common\Entity\Style\CellAlignment;
use SpoutX\Common\Entity\Style\CellVerticalAlignment;
use SpoutX\Common\Entity\Style\NumberFormat;
use SpoutX\Common\Entity\Style\Style;

/**
 * Class StyleBuilder
 * Builder to create new styles
 */
class StyleBuilder
{
    /** Style to be created */
    protected Style $style;

    public function __construct()
    {
        $this->style = new Style();
    }

    /**
     * Makes the font bold.
     */
    public function setFontBold(): self
    {
        $this->style->setFontBold();

        return $this;
    }

    /**
     * Makes the font italic.
     */
    public function setFontItalic(): self
    {
        $this->style->setFontItalic();

        return $this;
    }

    /**
     * Makes the font underlined.
     */
    public function setFontUnderline(): self
    {
        $this->style->setFontUnderline();

        return $this;
    }

    /**
     * Makes the font struck through.
     */
    public function setFontStrikethrough(): self
    {
        $this->style->setFontStrikethrough();

        return $this;
    }

    /**
     * Sets the font size.
     *
     * @param int $fontSize Font size, in pixels
     */
    public function setFontSize(int $fontSize): self
    {
        $this->style->setFontSize($fontSize);

        return $this;
    }

    /**
     * Sets the font color.
     *
     * @param string $fontColor ARGB color (@see Color)
     */
    public function setFontColor(string $fontColor): self
    {
        $this->style->setFontColor($fontColor);

        return $this;
    }

    /**
     * Sets the font name.
     *
     * @param string $fontName Name of the font to use
     */
    public function setFontName(string $fontName): self
    {
        $this->style->setFontName($fontName);

        return $this;
    }

    /**
     * Makes the text wrap in the cell if requested
     *
     * @param bool $shouldWrap Should the text be wrapped
     */
    public function setShouldWrapText(bool $shouldWrap = true): self
    {
        $this->style->setShouldWrapText($shouldWrap);

        return $this;
    }

    /**
     * Makes the text shrink to fit in the cell if requested
     *
     * @param bool $shouldShrink Should the text be shrunk
     */
    public function setShrinkToFit(bool $shouldShrink = false): self
    {
        $this->style->setShrinkToFit($shouldShrink);

        return $this;
    }

    /**
     * Set a border
     */
    public function setBorder(Border $border): self
    {
        $this->style->setBorder($border);

        return $this;
    }

    /**
     * Sets the number format.
     */
    public function setNumberFormat(NumberFormat $numberFormat): self
    {
        $this->style->setNumberFormat($numberFormat);

        return $this;
    }

    /**
     * Sets a background color
     *
     * @param string $color ARGB color (@see Color)
     */
    public function setBackgroundColor(string $color): self
    {
        $this->style->setBackgroundColor($color);

        return $this;
    }

    /**
     * Sets the horizontal align
     */
    public function setHorizontalAlign(CellAlignment $align): self
    {
        $this->style->setHorizontalAlign($align);

        return $this;
    }

    /**
     * Sets the vertical align
     */
    public function setVerticalAlign(CellVerticalAlignment $align): self
    {
        $this->style->setVerticalAlign($align);

        return $this;
    }

    /**
     * Set row height
     */
    public function setRowHeight(float $height): self
    {
        $this->style->setHeight($height);

        return $this;
    }

    /**
     * Sets a format
     *
     * @param string $format Format
     */
    public function setFormat(string $format): self
    {
        $this->style->setFormat($format);

        return $this;
    }

    /**
     * Returns the configured style. The style is cached and can be reused.
     */
    public function build(): Style
    {
        return $this->style;
    }
}

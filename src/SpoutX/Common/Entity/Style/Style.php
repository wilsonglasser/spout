<?php

declare(strict_types=1);

namespace SpoutX\Common\Entity\Style;

/**
 * Class Style
 * Represents a style to be applied to a cell
 */
class Style
{
    /** Default font values */
    public const DEFAULT_FONT_SIZE = 11;
    public const DEFAULT_FONT_COLOR = Color::BLACK;
    public const DEFAULT_FONT_NAME = 'Arial';

    /** Style ID */
    private ?int $id = null;

    /** Whether the font should be bold */
    private bool $fontBold = false;
    /** Whether the bold property was set */
    private bool $hasSetFontBold = false;

    /** Whether the font should be italic */
    private bool $fontItalic = false;
    /** Whether the italic property was set */
    private bool $hasSetFontItalic = false;

    /** Whether the font should be underlined */
    private bool $fontUnderline = false;
    /** Whether the underline property was set */
    private bool $hasSetFontUnderline = false;

    /** Whether the font should be struck through */
    private bool $fontStrikethrough = false;
    /** Whether the strikethrough property was set */
    private bool $hasSetFontStrikethrough = false;

    /** Font size */
    private int $fontSize = self::DEFAULT_FONT_SIZE;
    /** Whether the font size property was set */
    private bool $hasSetFontSize = false;

    /** Font color */
    private string $fontColor = self::DEFAULT_FONT_COLOR;
    /** Whether the font color property was set */
    private bool $hasSetFontColor = false;

    /** Font name */
    private string $fontName = self::DEFAULT_FONT_NAME;
    /** Whether the font name property was set */
    private bool $hasSetFontName = false;

    /** Whether specific font properties should be applied */
    private bool $shouldApplyFont = false;

    /** Whether the text should wrap in the cell (useful for long or multi-lines text) */
    private bool $shouldWrapText = false;
    /** Whether the wrap text property was set */
    private bool $hasSetWrapText = false;

    /** Text need to shrink to fit */
    private bool $shrinkToFit = false;

    /** Horizontal align */
    private ?CellAlignment $horizontalAlign = null;

    /** Vertical align */
    private CellVerticalAlignment $verticalAlign = CellVerticalAlignment::Center;

    private ?Border $border = null;

    public bool $isEmpty = false;

    /** Whether border properties should be applied */
    private bool $shouldApplyBorder = false;

    /** Background color */
    private ?string $backgroundColor = null;

    private bool $hasSetBackgroundColor = false;

    /** Row height */
    private ?float $height = null;

    private ?NumberFormat $numberFormat = null;

    /** Format */
    private ?string $format = null;

    private bool $hasSetFormat = false;

    private bool $isRegistered = false;

    private static ?self $instance = null;

    public static function defaultStyle(): self
    {
        if (self::$instance === null) {
            self::$instance = new Style();
        }

        return self::$instance;
    }

    /**
     * Get row height
     */
    public function getHeight(): ?float
    {
        return $this->height;
    }

    /**
     * Set row height
     */
    public function setHeight(float $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getBorder(): ?Border
    {
        return $this->border;
    }

    public function setBorder(Border $border): self
    {
        $this->shouldApplyBorder = true;
        $this->border = $border;

        return $this;
    }

    public function shouldApplyBorder(): bool
    {
        return $this->shouldApplyBorder;
    }

    public function isFontBold(): bool
    {
        return $this->fontBold;
    }

    public function setFontBold(): self
    {
        $this->fontBold = true;
        $this->hasSetFontBold = true;
        $this->shouldApplyFont = true;

        return $this;
    }

    public function hasSetFontBold(): bool
    {
        return $this->hasSetFontBold;
    }

    public function isFontItalic(): bool
    {
        return $this->fontItalic;
    }

    public function setFontItalic(): self
    {
        $this->fontItalic = true;
        $this->hasSetFontItalic = true;
        $this->shouldApplyFont = true;

        return $this;
    }

    public function hasSetFontItalic(): bool
    {
        return $this->hasSetFontItalic;
    }

    public function isFontUnderline(): bool
    {
        return $this->fontUnderline;
    }

    public function setFontUnderline(): self
    {
        $this->fontUnderline = true;
        $this->hasSetFontUnderline = true;
        $this->shouldApplyFont = true;

        return $this;
    }

    public function hasSetFontUnderline(): bool
    {
        return $this->hasSetFontUnderline;
    }

    public function isFontStrikethrough(): bool
    {
        return $this->fontStrikethrough;
    }

    public function setFontStrikethrough(): self
    {
        $this->fontStrikethrough = true;
        $this->hasSetFontStrikethrough = true;
        $this->shouldApplyFont = true;

        return $this;
    }

    public function hasSetFontStrikethrough(): bool
    {
        return $this->hasSetFontStrikethrough;
    }

    public function getFontSize(): int
    {
        return $this->fontSize;
    }

    public function setFontSize(int $fontSize): self
    {
        $this->fontSize = $fontSize;
        $this->hasSetFontSize = true;
        $this->shouldApplyFont = true;

        return $this;
    }

    public function hasSetFontSize(): bool
    {
        return $this->hasSetFontSize;
    }

    public function getFontColor(): string
    {
        return $this->fontColor;
    }

    /**
     * Sets the font color.
     *
     * @param string $fontColor ARGB color (@see Color)
     */
    public function setFontColor(string $fontColor): self
    {
        $this->fontColor = $fontColor;
        $this->hasSetFontColor = true;
        $this->shouldApplyFont = true;

        return $this;
    }

    public function hasSetFontColor(): bool
    {
        return $this->hasSetFontColor;
    }

    public function getFontName(): string
    {
        return $this->fontName;
    }

    public function setFontName(string $fontName): self
    {
        $this->fontName = $fontName;
        $this->hasSetFontName = true;
        $this->shouldApplyFont = true;

        return $this;
    }

    public function hasSetFontName(): bool
    {
        return $this->hasSetFontName;
    }

    public function shouldWrapText(): bool
    {
        return $this->shouldWrapText;
    }

    public function setShouldWrapText(bool $shouldWrap = true): self
    {
        $this->shouldWrapText = $shouldWrap;
        $this->hasSetWrapText = true;

        return $this;
    }

    public function hasSetWrapText(): bool
    {
        return $this->hasSetWrapText;
    }

    /**
     * @return bool Whether specific font properties should be applied
     */
    public function shouldApplyFont(): bool
    {
        return $this->shouldApplyFont;
    }

    public function getVerticalAlign(): CellVerticalAlignment
    {
        return $this->verticalAlign;
    }

    public function setVerticalAlign(CellVerticalAlignment $verticalAlign): self
    {
        $this->verticalAlign = $verticalAlign;

        return $this;
    }

    public function getHorizontalAlign(): ?CellAlignment
    {
        return $this->horizontalAlign;
    }

    public function setHorizontalAlign(CellAlignment $horizontalAlign): self
    {
        $this->horizontalAlign = $horizontalAlign;

        return $this;
    }

    /**
     * Sets shrink to fit
     */
    public function setShrinkToFit(bool $shouldShrink = false): self
    {
        $this->shrinkToFit = $shouldShrink;

        return $this;
    }

    public function getShrinkToFit(): bool
    {
        return $this->shrinkToFit;
    }

    /**
     * Sets the background color
     *
     * @param string $color ARGB color (@see Color)
     */
    public function setBackgroundColor(string $color): self
    {
        $this->hasSetBackgroundColor = true;
        $this->backgroundColor = $color;

        return $this;
    }

    public function getBackgroundColor(): ?string
    {
        return $this->backgroundColor;
    }

    /**
     * @return bool Whether the background color should be applied
     */
    public function shouldApplyBackgroundColor(): bool
    {
        return $this->hasSetBackgroundColor;
    }

    /**
     * Sets format
     */
    public function setFormat(string $format): self
    {
        $this->hasSetFormat = true;
        $this->format = $format;
        $this->isEmpty = false;

        return $this;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    /**
     * @return bool Whether format should be applied
     */
    public function shouldApplyFormat(): bool
    {
        return $this->hasSetFormat;
    }

    public function isRegistered(): bool
    {
        return $this->isRegistered;
    }

    public function markAsRegistered(?int $id): void
    {
        $this->setId($id);
        $this->isRegistered = true;
    }

    public function unmarkAsRegistered(): void
    {
        $this->setId(0);
        $this->isRegistered = false;
    }

    public function isEmpty(): bool
    {
        return $this->isEmpty;
    }

    public function getNumberFormat(): ?NumberFormat
    {
        return $this->numberFormat;
    }

    /**
     * Sets the number format.
     */
    public function setNumberFormat(NumberFormat $numberFormat): self
    {
        $this->numberFormat = $numberFormat;

        return $this;
    }
}

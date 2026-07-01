<?php

declare(strict_types=1);

namespace SpoutX\Writer\Common\Entity;

use SpoutX\Common\Entity\Style\Style;

/**
 * Class Cell
 */
class Comment
{
    public const DEFAULT_BACKGROUND_COLOR = '#FFFFE1';

    /** The comment cell */
    protected string $cell;

    /** The comment */
    protected ?string $text;

    /** Comment authors */
    protected ?string $author;

    /** Comment author id */
    protected $authorId;

    /** The cell style */
    protected Style $style;

    /** Comment width (CSS style, i.e. XXpx or YYpt). */
    private string $width = '96pt';

    /** Left margin (CSS style, i.e. XXpx or YYpt). */
    private string $marginLeft = '59.25pt';

    /** Top margin (CSS style, i.e. XXpx or YYpt). */
    private string $marginTop = '1.5pt';

    /** Visible. */
    private bool $visible = false;

    /** Comment height (CSS style, i.e. XXpx or YYpt). */
    private string $height = '55.5pt';

    public function __construct(string $cell, ?string $text, ?string $author = null, ?Style $style = null)
    {
        $this->setCell($cell);
        $this->setText($text);
        $this->setAuthor($author);
        $this->setStyle($style);
    }

    public function setText(?string $text): void
    {
        $this->text = $text;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setAuthor(?string $author): void
    {
        $this->author = $author;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthorId($authorId)
    {
        $this->authorId = $authorId;
    }

    public function getAuthorId()
    {
        return $this->authorId;
    }

    public function setCell(string $cell): void
    {
        $this->cell = $cell;
    }

    public function getCell(): string
    {
        return $this->cell;
    }

    public function setStyle(?Style $style): void
    {
        $this->style = $style ?: new Style();
    }

    public function getStyle(): Style
    {
        return $this->style;
    }

    public function setVisible(bool $visible): void
    {
        $this->visible = $visible;
    }

    /**
     * Get hash code.
     *
     * @return string Hash code
     */
    public function getHashCode(): string
    {
        return md5(
            $this->author .
            $this->text .
            $this->width .
            $this->height .
            $this->marginLeft .
            $this->marginTop .
            ($this->visible ? 1 : 0) .
            $this->alignment .
            __CLASS__
        );
    }


    public function getVisible(): bool
    {
        return $this->visible;
    }

    /**
     * Get comment width (CSS style, i.e. XXpx or YYpt).
     */
    public function getWidth(): string
    {
        return $this->width;
    }

    /**
     * Set comment width (CSS style, i.e. XXpx or YYpt).
     *
     * @return $this
     */
    public function setWidth(string $width): self
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Get comment height (CSS style, i.e. XXpx or YYpt).
     */
    public function getHeight(): string
    {
        return $this->height;
    }

    /**
     * Set comment height (CSS style, i.e. XXpx or YYpt).
     *
     * @return $this
     */
    public function setHeight(string $value): self
    {
        $this->height = $value;

        return $this;
    }

    /**
     * Get left margin (CSS style, i.e. XXpx or YYpt).
     */
    public function getMarginLeft(): string
    {
        return $this->marginLeft;
    }

    /**
     * Set left margin (CSS style, i.e. XXpx or YYpt).
     *
     * @return $this
     */
    public function setMarginLeft(string $value): self
    {
        $this->marginLeft = $value;

        return $this;
    }

    /**
     * Get top margin (CSS style, i.e. XXpx or YYpt).
     */
    public function getMarginTop(): string
    {
        return $this->marginTop;
    }

    /**
     * Set top margin (CSS style, i.e. XXpx or YYpt).
     *
     * @return $this
     */
    public function setMarginTop(string $value): self
    {
        $this->marginTop = $value;

        return $this;
    }
}

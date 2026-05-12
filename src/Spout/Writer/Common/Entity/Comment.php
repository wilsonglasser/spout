<?php

namespace WilsonGlasser\Spout\Writer\Common\Entity;


use WilsonGlasser\Spout\Common\Entity\Style\Style;
/**
 * Class Cell
 */
class Comment
{

    const DEFAULT_BACKGROUND_COLOR = '#FFFFE1';
    
    /**
     * The comment cell
     * @var string
     */
    protected $cell;

    /**
     * The comment
     * @var string
     */
    protected $text;

    /**
     * Comment authors
     * @var string
     */
    protected $author;
    /**
     * Comment author id
     * @var int
     */
    protected $authorId;

    /**
     * The cell style
     * @var Style
     */
    protected $style;

    /**
     * Comment width (CSS style, i.e. XXpx or YYpt).
     *
     * @var string
     */
    private $width = '96pt';

    /**
     * Left margin (CSS style, i.e. XXpx or YYpt).
     *
     * @var string
     */
    private $marginLeft = '59.25pt';

    /**
     * Top margin (CSS style, i.e. XXpx or YYpt).
     *
     * @var string
     */
    private $marginTop = '1.5pt';

    /**
     * Visible.
     *
     * @var bool
     */
    private $visible = false;

    /**
     * Comment height (CSS style, i.e. XXpx or YYpt).
     *
     * @var string
     */
    private $height = '55.5pt';

    /**
     * @param $value mixed
     * @param Style|null $style
     */
    public function __construct($cell, $text, $author = null, ?Style $style = null)
    {
        $this->setCell($cell);
        $this->setText($text);
        $this->setAuthor($author);
        $this->setStyle($style);
    }

    /**
     * @param string
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @return string|null
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }
    /**
     * @return string|null
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param string
     */
    public function setAuthorId($authorId)
    {
        $this->authorId = $authorId;
    }
    /**
     * @return string|null
     */
    public function getAuthorId()
    {
        return $this->authorId;
    }

    /**
     * @param string
     */
    public function setCell($cell)
    {
        $this->cell = $cell;
    }
    /**
     * @return string|null
     */
    public function getCell()
    {
        return $this->cell;
    }

    /**
     * @param Style|null $style
     */
    public function setStyle($style)
    {
        $this->style = $style ?: new Style();
    }

    /**
     * @return Style
     */
    public function getStyle()
    {
        return $this->style;
    }

    /**
     * @param $visible
     */
    public function setVisible($visible) {
        $this->visible = $visible;
    }

    /**
     * Get hash code.
     *
     * @return string Hash code
     */
    public function getHashCode()
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


    /**
     * @return bool
     */
    public function getVisible() {
        return $this->visible;
    }
    /**
     * Get comment width (CSS style, i.e. XXpx or YYpt).
     *
     * @return string
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set comment width (CSS style, i.e. XXpx or YYpt).
     *
     * @param string $width
     *
     * @return $this
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Get comment height (CSS style, i.e. XXpx or YYpt).
     *
     * @return string
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Set comment height (CSS style, i.e. XXpx or YYpt).
     *
     * @param string $value
     *
     * @return $this
     */
    public function setHeight($value)
    {
        $this->height = $value;

        return $this;
    }

    /**
     * Get left margin (CSS style, i.e. XXpx or YYpt).
     *
     * @return string
     */
    public function getMarginLeft()
    {
        return $this->marginLeft;
    }

    /**
     * Set left margin (CSS style, i.e. XXpx or YYpt).
     *
     * @param string $value
     *
     * @return $this
     */
    public function setMarginLeft($value)
    {
        $this->marginLeft = $value;

        return $this;
    }

    /**
     * Get top margin (CSS style, i.e. XXpx or YYpt).
     *
     * @return string
     */
    public function getMarginTop()
    {
        return $this->marginTop;
    }

    /**
     * Set top margin (CSS style, i.e. XXpx or YYpt).
     *
     * @param string $value
     *
     * @return $this
     */
    public function setMarginTop($value)
    {
        $this->marginTop = $value;

        return $this;
    }
}

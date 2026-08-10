<?php

declare(strict_types=1);

namespace SpoutX\Common\Entity\Style;

/**
 * One edge of a cell border. Validity of name/style/width is guaranteed by the
 * enum types, so no runtime validation is needed.
 */
class BorderPart
{
    /**
     * @param BorderName  $name  The edge this part applies to
     * @param string      $color An RGB color code
     * @param BorderWidth $width The line width
     * @param BorderStyle $style The line style
     */
    public function __construct(
        protected BorderName $name,
        protected string $color = Color::BLACK,
        protected BorderWidth $width = BorderWidth::Medium,
        protected BorderStyle $style = BorderStyle::Solid,
    ) {
    }

    public function getName(): BorderName
    {
        return $this->name;
    }

    public function setName(BorderName $name): void
    {
        $this->name = $name;
    }

    public function getStyle(): BorderStyle
    {
        return $this->style;
    }

    public function setStyle(BorderStyle $style): void
    {
        $this->style = $style;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): void
    {
        $this->color = $color;
    }

    public function getWidth(): BorderWidth
    {
        return $this->width;
    }

    public function setWidth(BorderWidth $width): void
    {
        $this->width = $width;
    }
}

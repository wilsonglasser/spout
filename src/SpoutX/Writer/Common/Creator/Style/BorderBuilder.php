<?php

declare(strict_types=1);

namespace SpoutX\Writer\Common\Creator\Style;

use SpoutX\Common\Entity\Style\Border;
use SpoutX\Common\Entity\Style\BorderName;
use SpoutX\Common\Entity\Style\BorderPart;
use SpoutX\Common\Entity\Style\BorderStyle;
use SpoutX\Common\Entity\Style\BorderWidth;
use SpoutX\Common\Entity\Style\Color;

class BorderBuilder
{
    protected Border $border;

    public function __construct()
    {
        $this->border = new Border();
    }

    public function setBorderTop(string $color = Color::BLACK, BorderWidth $width = BorderWidth::Medium, BorderStyle $style = BorderStyle::Solid): self
    {
        $this->border->addPart(new BorderPart(BorderName::Top, $color, $width, $style));

        return $this;
    }

    public function setBorderRight(string $color = Color::BLACK, BorderWidth $width = BorderWidth::Medium, BorderStyle $style = BorderStyle::Solid): self
    {
        $this->border->addPart(new BorderPart(BorderName::Right, $color, $width, $style));

        return $this;
    }

    public function setBorderBottom(string $color = Color::BLACK, BorderWidth $width = BorderWidth::Medium, BorderStyle $style = BorderStyle::Solid): self
    {
        $this->border->addPart(new BorderPart(BorderName::Bottom, $color, $width, $style));

        return $this;
    }

    public function setBorderLeft(string $color = Color::BLACK, BorderWidth $width = BorderWidth::Medium, BorderStyle $style = BorderStyle::Solid): self
    {
        $this->border->addPart(new BorderPart(BorderName::Left, $color, $width, $style));

        return $this;
    }

    public function build(): Border
    {
        return $this->border;
    }
}

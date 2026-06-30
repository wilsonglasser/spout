<?php

declare(strict_types=1);

namespace SpoutX\Common\Entity\Style;

/**
 * The line width of a border part.
 */
enum BorderWidth: string
{
    case Thin = 'thin';
    case Medium = 'medium';
    case Thick = 'thick';
}

<?php

declare(strict_types=1);

namespace SpoutX\Common\Entity\Style;

/**
 * The line style of a border part.
 */
enum BorderStyle: string
{
    case None = 'none';
    case Solid = 'solid';
    case Dashed = 'dashed';
    case Dotted = 'dotted';
    case Double = 'double';
}

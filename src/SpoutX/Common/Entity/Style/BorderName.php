<?php

declare(strict_types=1);

namespace SpoutX\Common\Entity\Style;

/**
 * Which edge of the cell a border part applies to.
 */
enum BorderName: string
{
    case Left = 'left';
    case Right = 'right';
    case Top = 'top';
    case Bottom = 'bottom';
}

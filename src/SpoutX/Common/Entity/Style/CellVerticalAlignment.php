<?php

declare(strict_types=1);

namespace SpoutX\Common\Entity\Style;

/**
 * Vertical alignment of a cell's content.
 */
enum CellVerticalAlignment: string
{
    case Top = 'top';
    case Center = 'center';
    case Bottom = 'bottom';
}

<?php

declare(strict_types=1);

namespace SpoutX\Common\Entity\Style;

/**
 * Horizontal alignment of a cell's content.
 */
enum CellAlignment: string
{
    case Left = 'left';
    case Right = 'right';
    case Center = 'center';
    case General = 'general';
}

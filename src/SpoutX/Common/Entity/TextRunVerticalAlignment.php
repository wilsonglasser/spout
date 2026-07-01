<?php

declare(strict_types=1);

namespace SpoutX\Common\Entity;

/**
 * Vertical alignment of a rich-text run (the <vertAlign> run property).
 */
enum TextRunVerticalAlignment: string
{
    case Subscript = 'subscript';
    case Superscript = 'superscript';
}

<?php

declare(strict_types=1);

namespace SpoutX\Common\Entity;

/**
 * The type of value held by a {@see Cell}.
 *
 * Backed by the historical integer values so the produced XLSX is byte-for-byte
 * identical to previous versions.
 */
enum CellType: int
{
    /** Whole numbers, fractional numbers, dates */
    case Numeric = 0;
    /** Text */
    case String = 1;
    /** Formula (value starts with "=") */
    case Formula = 2;
    /** Empty cell */
    case Empty = 3;
    /** Boolean */
    case Boolean = 4;
    /** Date */
    case Date = 5;
    /** Cell holding an error */
    case Error = 6;
}

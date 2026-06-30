<?php

declare(strict_types=1);

namespace SpoutX\Reader\Common\Entity;

/**
 * Class Options
 * Readers' options holder
 */
abstract class Options
{
    // Common options
    public const SHOULD_FORMAT_DATES = 'shouldFormatDates';
    public const SHOULD_PRESERVE_EMPTY_ROWS = 'shouldPreserveEmptyRows';

    // XLSX specific options
    public const TEMP_FOLDER = 'tempFolder';
    public const SHOULD_USE_1904_DATES = 'shouldUse1904Dates';
}

<?php

namespace SpoutX\Reader\Common\Entity;

/**
 * Class Options
 * Readers' options holder
 */
abstract class Options
{
    // Common options
    const SHOULD_FORMAT_DATES = 'shouldFormatDates';
    const SHOULD_PRESERVE_EMPTY_ROWS = 'shouldPreserveEmptyRows';

    // XLSX specific options
    const TEMP_FOLDER = 'tempFolder';
    const SHOULD_USE_1904_DATES = 'shouldUse1904Dates';
}

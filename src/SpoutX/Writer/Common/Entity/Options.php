<?php

declare(strict_types=1);

namespace SpoutX\Writer\Common\Entity;

/**
 * Class Options
 * Writers' options holder
 */
abstract class Options
{
    // Multisheets options
    public const TEMP_FOLDER = 'tempFolder';
    public const DEFAULT_ROW_STYLE = 'defaultRowStyle';
    public const SHOULD_CREATE_NEW_SHEETS_AUTOMATICALLY = 'shouldCreateNewSheetsAutomatically';

    // XLSX specific options
    public const SHOULD_USE_INLINE_STRINGS = 'shouldUseInlineStrings';
}

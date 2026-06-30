<?php

namespace SpoutX\Writer\Common\Entity;

/**
 * Class Options
 * Writers' options holder
 */
abstract class Options
{
    // Multisheets options
    const TEMP_FOLDER = 'tempFolder';
    const DEFAULT_ROW_STYLE = 'defaultRowStyle';
    const SHOULD_CREATE_NEW_SHEETS_AUTOMATICALLY = 'shouldCreateNewSheetsAutomatically';

    // XLSX specific options
    const SHOULD_USE_INLINE_STRINGS = 'shouldUseInlineStrings';
}

<?php

declare(strict_types=1);

namespace SpoutX\Reader\XLSX\Creator;

use SpoutX\Common\Helper\Escaper;
use SpoutX\Reader\XLSX\Helper\CellValueFormatter;
use SpoutX\Reader\XLSX\Manager\SharedStringsManager;
use SpoutX\Reader\XLSX\Manager\StyleManager;

/**
 * Class HelperFactory
 * Factory to create helpers
 */
class HelperFactory extends \SpoutX\Common\Creator\HelperFactory
{
    /**
     * @param SharedStringsManager $sharedStringsManager Manages shared strings
     * @param StyleManager $styleManager Manages styles
     * @param bool $shouldFormatDates Whether date/time values should be returned as PHP objects or be formatted as strings
     * @param bool $shouldUse1904Dates Whether date/time values should use a calendar starting in 1904 instead of 1900
     * @return CellValueFormatter
     */
    public function createCellValueFormatter(SharedStringsManager $sharedStringsManager, StyleManager $styleManager, bool $shouldFormatDates, bool $shouldUse1904Dates): CellValueFormatter
    {
        $escaper = $this->createStringsEscaper();

        return new CellValueFormatter($sharedStringsManager, $styleManager, $shouldFormatDates, $shouldUse1904Dates, $escaper);
    }

    /**
     * @return Escaper\XLSX
     */
    public function createStringsEscaper(): Escaper\XLSX
    {
        /* @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        return new Escaper\XLSX();
    }
}

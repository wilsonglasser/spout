<?php

declare(strict_types=1);

namespace SpoutX\Writer\Common\Manager;

use SpoutX\Common\Entity\Row;
use SpoutX\Common\Entity\Style\Style;
use SpoutX\Writer\Common\Entity\Worksheet;

/**
 * Interface WorksheetManagerInterface
 * Inteface for worksheet managers, providing the generic interfaces to work with worksheets.
 */
interface WorksheetManagerInterface
{
    /**
     * Adds a row to the worksheet.
     *
     * @param Worksheet $worksheet The worksheet to add the row to
     * @param Row|array $row The row to be added
     * @throws \SpoutX\Common\Exception\IOException If the data cannot be written
     * @throws \SpoutX\Common\Exception\InvalidArgumentException If a cell value's type is not supported
     */
    public function addRow(Worksheet $worksheet, Row|array $row): void;

    /**
     * Prepares the worksheet to accept data
     *
     * @param Worksheet $worksheet The worksheet to start
     * @throws \SpoutX\Common\Exception\IOException If the sheet data file cannot be opened for writing
     */
    public function startSheet(Worksheet $worksheet): void;

    /**
     * Closes the worksheet
     */
    public function close(Worksheet $worksheet, ?Style $defaultStyle = null): void;
}

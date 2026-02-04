<?php

namespace WilsonGlasser\Spout\Writer\Common\Manager;

use WilsonGlasser\Spout\Common\Entity\Row;
use WilsonGlasser\Spout\Common\Entity\Style\Style;
use WilsonGlasser\Spout\Writer\Common\Entity\Worksheet;

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
     * @throws \WilsonGlasser\Spout\Common\Exception\IOException If the data cannot be written
     * @throws \WilsonGlasser\Spout\Common\Exception\InvalidArgumentException If a cell value's type is not supported
     * @return void
     */
    public function addRow(Worksheet $worksheet, $row);

    /**
     * Prepares the worksheet to accept data
     *
     * @param Worksheet $worksheet The worksheet to start
     * @throws \WilsonGlasser\Spout\Common\Exception\IOException If the sheet data file cannot be opened for writing
     * @return void
     */
    public function startSheet(Worksheet $worksheet);

    /**
     * Closes the worksheet
     *
     * @param Worksheet $worksheet
     * @param Style $defaultStyle
     * @return void
     */
    public function close(Worksheet $worksheet, ?Style $defaultStyle = null);
}

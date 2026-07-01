<?php

declare(strict_types=1);

namespace SpoutX\Writer;

use SpoutX\Common\Entity\Row;
use SpoutX\Common\Entity\Style\Style;

/**
 * Interface WriterInterface
 */
interface WriterInterface
{
    /**
     * Initializes the writer and opens it to accept data.
     * By using this method, the data will be written to a file.
     *
     * @param  string $outputFilePath Path of the output file that will contain the data
     * @throws \SpoutX\Common\Exception\IOException If the writer cannot be opened or if the given path is not writable
     */
    public function openToFile(string $outputFilePath): self;

    /**
     * Initializes the writer and opens it to accept data.
     * By using this method, the data will be outputted directly to the browser.
     *
     * @param  string $outputFileName Name of the output file that will contain the data. If a path is passed in, only the file name will be kept
     * @throws \SpoutX\Common\Exception\IOException If the writer cannot be opened
     */
    public function openToBrowser(string $outputFileName): self;

    /**
     * Sets the default styles for all rows added with "addRow".
     * Overriding the default style instead of using "addRowWithStyle" improves performance by 20%.
     * @see https://github.com/box/spout/issues/272
     */
    public function setDefaultRowStyle(Style $defaultStyle): self;


    /**
     * Appends a row to the end of the stream.
     *
     * @param Row|array $row The row to be appended to the stream
     * @throws \SpoutX\Writer\Exception\WriterNotOpenedException If the writer has not been opened yetthe writer
     * @throws \SpoutX\Common\Exception\IOException If unable to write data
     */
    public function addRow(Row|array $row): self;

    /**
     * Appends the rows to the end of the stream.
     *
     * @param Row[]|array $rows The rows to be appended to the stream
     * @throws \SpoutX\Common\Exception\InvalidArgumentException If the input param is not valid
     * @throws \SpoutX\Writer\Exception\WriterNotOpenedException If the writer has not been opened yet
     * @throws \SpoutX\Common\Exception\IOException If unable to write data
     */
    public function addRows(array $rows): self;

    /**
     * Closes the writer. This will close the streamer as well, preventing new data
     * to be written to the file.
     */
    public function close(): void;
}

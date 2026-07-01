<?php

declare(strict_types=1);

namespace SpoutX\Writer\XLSX;

use SpoutX\Writer\Common\Entity\Options;
use SpoutX\Writer\WriterMultiSheetsAbstract;

/**
 * Class Writer
 * This class provides base support to write data to XLSX files
 */
class Writer extends WriterMultiSheetsAbstract
{
    /** @var string Content-Type value for the header */
    protected static string $headerContentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    /**
     * Sets a custom temporary folder for creating intermediate files/folders.
     * This must be set before opening the writer.
     *
     * @param string $tempFolder Temporary folder where the files to create the XLSX will be stored
     * @throws \SpoutX\Writer\Exception\WriterAlreadyOpenedException If the writer was already opened
     */
    public function setTempFolder(string $tempFolder): self
    {
        $this->throwIfWriterAlreadyOpened('Writer must be configured before opening it.');

        $this->optionsManager->setOption(Options::TEMP_FOLDER, $tempFolder);

        return $this;
    }

    /**
     * Use inline string to be more memory efficient. If set to false, it will use shared strings.
     * This must be set before opening the writer.
     *
     * @param bool $shouldUseInlineStrings Whether inline or shared strings should be used
     * @throws \SpoutX\Writer\Exception\WriterAlreadyOpenedException If the writer was already opened
     */
    public function setShouldUseInlineStrings(bool $shouldUseInlineStrings): self
    {
        $this->throwIfWriterAlreadyOpened('Writer must be configured before opening it.');

        $this->optionsManager->setOption(Options::SHOULD_USE_INLINE_STRINGS, $shouldUseInlineStrings);

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace SpoutX\Reader;

use SpoutX\Common\Exception\IOException;
use SpoutX\Common\Helper\GlobalFunctionsHelper;
use SpoutX\Common\Manager\OptionsManagerInterface;
use SpoutX\Reader\Common\Creator\InternalEntityFactoryInterface;
use SpoutX\Reader\Common\Entity\Options;
use SpoutX\Reader\Exception\ReaderNotOpenedException;

/**
 * Class ReaderAbstract
 *
 * @abstract
 */
abstract class ReaderAbstract implements ReaderInterface
{
    /** @var bool Indicates whether the stream is currently open */
    protected bool $isStreamOpened = false;

    /** @var InternalEntityFactoryInterface Factory to create entities */
    protected InternalEntityFactoryInterface $entityFactory;

    /** @var \SpoutX\Common\Helper\GlobalFunctionsHelper Helper to work with global functions */
    protected GlobalFunctionsHelper $globalFunctionsHelper;

    /** @var OptionsManagerInterface Writer options manager */
    protected OptionsManagerInterface $optionsManager;

    /**
     * Returns whether stream wrappers are supported
     */
    abstract protected function doesSupportStreamWrapper(): bool;

    /**
     * Opens the file at the given file path to make it ready to be read
     *
     * @param  string $filePath Path of the file to be read
     */
    abstract protected function openReader(string $filePath): void;

    /**
     * Returns an iterator to iterate over sheets.
     *
     * @return IteratorInterface To iterate over sheets
     */
    abstract protected function getConcreteSheetIterator(): IteratorInterface;

    /**
     * Closes the reader. To be used after reading the file.
     */
    abstract protected function closeReader(): void;

    public function __construct(
        OptionsManagerInterface $optionsManager,
        GlobalFunctionsHelper $globalFunctionsHelper,
        InternalEntityFactoryInterface $entityFactory
    ) {
        $this->optionsManager = $optionsManager;
        $this->globalFunctionsHelper = $globalFunctionsHelper;
        $this->entityFactory = $entityFactory;
    }

    /**
     * Sets whether date/time values should be returned as PHP objects or be formatted as strings.
     *
     * @param bool $shouldFormatDates
     * @return ReaderAbstract
     */
    public function setShouldFormatDates(bool $shouldFormatDates): self
    {
        $this->optionsManager->setOption(Options::SHOULD_FORMAT_DATES, $shouldFormatDates);

        return $this;
    }

    /**
     * Sets whether empty rows should be returned or skipped.
     *
     * @param bool $shouldPreserveEmptyRows
     * @return ReaderAbstract
     */
    public function setShouldPreserveEmptyRows(bool $shouldPreserveEmptyRows): self
    {
        $this->optionsManager->setOption(Options::SHOULD_PRESERVE_EMPTY_ROWS, $shouldPreserveEmptyRows);

        return $this;
    }

    /**
     * Prepares the reader to read the given file. It also makes sure
     * that the file exists and is readable.
     *
     * @param  string $filePath Path of the file to be read
     * @throws \SpoutX\Common\Exception\IOException If the file at the given path does not exist, is not readable or is corrupted
     */
    public function open(string $filePath): void
    {
        if ($this->isStreamWrapper($filePath) && (!$this->doesSupportStreamWrapper() || !$this->isSupportedStreamWrapper($filePath))) {
            throw new IOException("Could not open $filePath for reading! Stream wrapper used is not supported for this type of file.");
        }

        if (!$this->isPhpStream($filePath)) {
            // we skip the checks if the provided file path points to a PHP stream
            if (!$this->globalFunctionsHelper->file_exists($filePath)) {
                throw new IOException("Could not open $filePath for reading! File does not exist.");
            }
            if (!$this->globalFunctionsHelper->is_readable($filePath)) {
                throw new IOException("Could not open $filePath for reading! File is not readable.");
            }
        }

        try {
            $fileRealPath = $this->getFileRealPath($filePath);
            $this->openReader($fileRealPath);
            $this->isStreamOpened = true;
        } catch (\Exception $exception) {
            throw new IOException("Could not open $filePath for reading! ({$exception->getMessage()})");
        }
    }

    /**
     * Returns the real path of the given path.
     * If the given path is a valid stream wrapper, returns the path unchanged.
     *
     * @param string $filePath
     * @return string
     */
    protected function getFileRealPath(string $filePath)
    {
        if ($this->isSupportedStreamWrapper($filePath)) {
            return $filePath;
        }

        // Need to use realpath to fix "Can't open file" on some Windows setup
        return realpath($filePath);
    }

    /**
     * Returns the scheme of the custom stream wrapper, if the path indicates a stream wrapper is used.
     * For example, php://temp => php, s3://path/to/file => s3...
     *
     * @param string $filePath Path of the file to be read
     * @return string|null The stream wrapper scheme or NULL if not a stream wrapper
     */
    protected function getStreamWrapperScheme(string $filePath): ?string
    {
        $streamScheme = null;
        if (preg_match('/^(\w+):\/\//', $filePath, $matches)) {
            $streamScheme = $matches[1];
        }

        return $streamScheme;
    }

    /**
     * Checks if the given path is an unsupported stream wrapper
     * (like local path, php://temp, mystream://foo/bar...).
     *
     * @param string $filePath Path of the file to be read
     * @return bool Whether the given path is an unsupported stream wrapper
     */
    protected function isStreamWrapper(string $filePath): bool
    {
        return ($this->getStreamWrapperScheme($filePath) !== null);
    }

    /**
     * Checks if the given path is an supported stream wrapper
     * (like php://temp, mystream://foo/bar...).
     * If the given path is a local path, returns true.
     *
     * @param string $filePath Path of the file to be read
     * @return bool Whether the given path is an supported stream wrapper
     */
    protected function isSupportedStreamWrapper(string $filePath): bool
    {
        $streamScheme = $this->getStreamWrapperScheme($filePath);

        return ($streamScheme !== null) ?
            in_array($streamScheme, $this->globalFunctionsHelper->stream_get_wrappers()) :
            true;
    }

    /**
     * Checks if a path is a PHP stream (like php://output, php://memory, ...)
     *
     * @param string $filePath Path of the file to be read
     * @return bool Whether the given path maps to a PHP stream
     */
    protected function isPhpStream(string $filePath): bool
    {
        $streamScheme = $this->getStreamWrapperScheme($filePath);

        return ($streamScheme === 'php');
    }

    /**
     * Returns an iterator to iterate over sheets.
     *
     * @throws \SpoutX\Reader\Exception\ReaderNotOpenedException If called before opening the reader
     * @return \Iterator To iterate over sheets
     */
    public function getSheetIterator(): \Iterator
    {
        if (!$this->isStreamOpened) {
            throw new ReaderNotOpenedException('Reader should be opened first.');
        }

        return $this->getConcreteSheetIterator();
    }

    /**
     * Closes the reader, preventing any additional reading
     */
    public function close(): void
    {
        if ($this->isStreamOpened) {
            $this->closeReader();

            $sheetIterator = $this->getConcreteSheetIterator();
            if ($sheetIterator) {
                $sheetIterator->end();
            }

            $this->isStreamOpened = false;
        }
    }
}

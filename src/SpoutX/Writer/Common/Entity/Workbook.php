<?php

declare(strict_types=1);

namespace SpoutX\Writer\Common\Entity;

use SpoutX\Writer\XLSX\Entity\DocumentProperties;
use SpoutX\Writer\XLSX\Entity\WorkbookProtection;

/**
 * Class Workbook
 * Entity describing a workbook
 */
class Workbook
{
    /** @var Worksheet[] List of the workbook's sheets */
    private array $worksheets = [];

    /** @var string Timestamp based unique ID identifying the workbook */
    private string $internalId;

    /** @var WorkbookProtection|null Workbook-level protection */
    private ?WorkbookProtection $protection = null;

    /** @var DocumentProperties|null Document metadata (title/author/...) */
    private ?DocumentProperties $documentProperties = null;

    /**
     * Workbook constructor.
     */
    public function __construct()
    {
        $this->internalId = uniqid();
    }

    /**
     * @return Worksheet[]
     */
    public function getWorksheets(): array
    {
        return $this->worksheets;
    }

    /**
     * @param Worksheet[] $worksheets
     */
    public function setWorksheets(array $worksheets): void
    {
        $this->worksheets = $worksheets;
    }

    public function getInternalId(): string
    {
        return $this->internalId;
    }

    public function getProtection(): ?WorkbookProtection
    {
        return $this->protection;
    }

    public function setProtection(?WorkbookProtection $protection): void
    {
        $this->protection = $protection;
    }

    public function getDocumentProperties(): ?DocumentProperties
    {
        return $this->documentProperties;
    }

    public function setDocumentProperties(?DocumentProperties $documentProperties): void
    {
        $this->documentProperties = $documentProperties;
    }
}

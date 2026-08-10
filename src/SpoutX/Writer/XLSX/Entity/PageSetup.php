<?php

declare(strict_types=1);

namespace SpoutX\Writer\XLSX\Entity;

/**
 * Print page setup: orientation, paper size and fit-to-page scaling.
 */
class PageSetup
{
    public function __construct(
        public ?PageOrientation $pageOrientation = null,
        public ?PaperSize $paperSize = null,
        public ?int $fitToHeight = null,
        public ?int $fitToWidth = null,
    ) {
    }

    /**
     * Whether the sheet should be scaled to fit a number of pages
     * (drives the <pageSetUpPr fitToPage="..."/> hint).
     */
    public function isFitToPage(): bool
    {
        return $this->fitToHeight !== null || $this->fitToWidth !== null;
    }
}

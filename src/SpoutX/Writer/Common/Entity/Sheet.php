<?php

declare(strict_types=1);

namespace SpoutX\Writer\Common\Entity;

use SpoutX\Common\Entity\ColumnDimension;
use SpoutX\Common\Entity\Style\Style;
use SpoutX\Writer\Common\Manager\SheetManager;
use SpoutX\Writer\Exception\InvalidSheetNameException ;
use SpoutX\Writer\XLSX\Entity\HeaderFooter;
use SpoutX\Writer\XLSX\Entity\PageMargin;
use SpoutX\Writer\XLSX\Entity\PageSetup;
use SpoutX\Writer\XLSX\Entity\SheetView;

/**
 * Class Sheet
 * External representation of a worksheet
 */
class Sheet
{
    public const DEFAULT_SHEET_NAME_PREFIX = 'Sheet';

    /** @var int Index of the sheet, based on order in the workbook (zero-based) */
    private int $index;

    /** @var string ID of the sheet's associated workbook. Used to restrict sheet name uniqueness enforcement to a single workbook */
    private string $associatedWorkbookId;

    /** @var string Name of the sheet */
    private string $name;

    /** @var bool Visibility of the sheet */
    private bool $isVisible;

    /** @var SheetManager Sheet manager */
    private SheetManager $sheetManager;

    /** @var string Range for auto Filter */
    private ?string $autoFilter = null;

    /** @var ColumnDimension[] Columns widths */
    private array $columnsDimensions = [];

    /** @var array Cell merges */
    private array $mergeCells = [];

    /** @var Comment[] Comments  */
    private array $comments = [];

    /** @var PageSetup|null Print orientation / paper size / fit-to-page */
    private ?PageSetup $pageSetup = null;

    /** @var PageMargin|null Print margins */
    private ?PageMargin $pageMargin = null;

    /** @var HeaderFooter|null Print header/footer */
    private ?HeaderFooter $headerFooter = null;

    /** @var SheetView|null Sheet view (freeze panes, zoom, gridlines, RTL) */
    private ?SheetView $sheetView = null;

    /**
     * @param int $sheetIndex Index of the sheet, based on order in the workbook (zero-based)
     * @param string $associatedWorkbookId ID of the sheet's associated workbook
     * @param SheetManager $sheetManager To manage sheets
     * @throws
     */
    public function __construct(int $sheetIndex, string $associatedWorkbookId, SheetManager $sheetManager)
    {
        $this->index = $sheetIndex;
        $this->associatedWorkbookId = $associatedWorkbookId;

        $this->sheetManager = $sheetManager;
        $this->sheetManager->markWorkbookIdAsUsed($associatedWorkbookId);

        $this->setName(self::DEFAULT_SHEET_NAME_PREFIX . ($sheetIndex + 1));
        $this->setIsVisible(true);
    }

    /**
     * @return int Index of the sheet, based on order in the workbook (zero-based)
     */
    public function getIndex(): int
    {
        return $this->index;
    }

    public function getAssociatedWorkbookId(): string
    {
        return $this->associatedWorkbookId;
    }

    /**
     * @return string Name of the sheet
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the name of the sheet. Note that Excel has some restrictions on the name:
     *  - it should not be blank
     *  - it should not exceed 31 characters
     *  - it should not contain these characters: \ / ? * : [ or ]
     *  - it should be unique
     *
     * @param string $name Name of the sheet
     * @throws InvalidSheetNameException If the sheet's name is invalid.
     * @return Sheet
     */
    public function setName(string $name): self
    {
        $this->sheetManager->throwIfNameIsInvalid($name, $this);

        $this->name = $name;

        $this->sheetManager->markSheetNameAsUsed($this);

        return $this;
    }

    public function getAutoFilter(): ?string
    {
        return $this->autoFilter;
    }

    public function setAutoFilter(string $range): void
    {
        $this->autoFilter = $range;
    }

    public function getMergeCells(): array
    {
        return $this->mergeCells;
    }

    /**
     * @param string $range Cells range
     */
    public function mergeCells(string $range): void
    {
        $this->mergeCells[] = $range;
    }


    /**
     * @return Comment[]
     */
    public function getComments(): array
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): void
    {
        $this->comments[] = $comment;
    }

    public function getPageSetup(): ?PageSetup
    {
        return $this->pageSetup;
    }

    public function setPageSetup(?PageSetup $pageSetup): self
    {
        $this->pageSetup = $pageSetup;

        return $this;
    }

    public function getPageMargin(): ?PageMargin
    {
        return $this->pageMargin;
    }

    public function setPageMargin(?PageMargin $pageMargin): self
    {
        $this->pageMargin = $pageMargin;

        return $this;
    }

    public function getHeaderFooter(): ?HeaderFooter
    {
        return $this->headerFooter;
    }

    public function setHeaderFooter(?HeaderFooter $headerFooter): self
    {
        $this->headerFooter = $headerFooter;

        return $this;
    }

    public function getSheetView(): ?SheetView
    {
        return $this->sheetView;
    }

    public function setSheetView(?SheetView $sheetView): self
    {
        $this->sheetView = $sheetView;

        return $this;
    }

    /**
     * @return ColumnDimension[]
     */
    public function getColumnDimensions(): array
    {
        return $this->columnsDimensions;
    }

    public function addColumnDimension(ColumnDimension $columnDimension): void
    {
        $this->columnsDimensions[] = $columnDimension;
    }

    /**
     * @param ColumnDimension[] $dimensions
     */
    public function setColumnDimensions(array $dimensions): void
    {
        $this->columnsDimensions = $dimensions;
    }

    /**
     * @return bool isVisible Visibility of the sheet
     */
    public function isVisible(): bool
    {
        return $this->isVisible;
    }

    /**
     * @param bool $isVisible Visibility of the sheet
     * @return Sheet
     */
    public function setIsVisible(bool $isVisible): self
    {
        $this->isVisible = $isVisible;

        return $this;
    }

    /**
     * Calculate widths for auto-size columns
     *
     * @param int[] $columnMaxLengths
     * @return Sheet
     */
    public function calculateColumnWidths(array $columnMaxLengths, ?Style $defaultStyle = null): self
    {

        foreach ($this->getColumnDimensions() as $colDimension) {
            if ($colDimension->getAutoSize() && isset($columnMaxLengths[$colDimension->getColumnIndex()])) {
                $width = ColumnDimension::calculateColumnWidth($columnMaxLengths[$colDimension->getColumnIndex()], $defaultStyle); // tem q ver se tem style D: );
                $colDimension->setWidth($width > 0 ? $width : ColumnDimension::DEFAULT_COLUMN_WIDTH);
            }
        }

        return $this;
    }

}

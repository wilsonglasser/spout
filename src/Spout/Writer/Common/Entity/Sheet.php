<?php

namespace WilsonGlasser\Spout\Writer\Common\Entity;

use WilsonGlasser\Spout\Common\Entity\ColumnDimension;
use WilsonGlasser\Spout\Common\Entity\Style\Style;
use WilsonGlasser\Spout\Writer\Exception\InvalidSheetNameException ;
use WilsonGlasser\Spout\Writer\Common\Manager\SheetManager;

/**
 * Class Sheet
 * External representation of a worksheet
 */
class Sheet
{
    const DEFAULT_SHEET_NAME_PREFIX = 'Sheet';

    /** @var int Index of the sheet, based on order in the workbook (zero-based) */
    private $index;

    /** @var string ID of the sheet's associated workbook. Used to restrict sheet name uniqueness enforcement to a single workbook */
    private $associatedWorkbookId;

    /** @var string Name of the sheet */
    private $name;

    /** @var bool Visibility of the sheet */
    private $isVisible;

    /** @var SheetManager Sheet manager */
    private $sheetManager;

    /** @var string Range for auto Filter */
    private $autoFilter;

    /** @var ColumnDimension[] Columns widths */
    private $columnsDimensions = [];

    /** @var array Cell merges */
    private $mergeCells = [];

    /** @var Comment[] Comments  */
    private $comments = [];

    /**
     * @param int $sheetIndex Index of the sheet, based on order in the workbook (zero-based)
     * @param string $associatedWorkbookId ID of the sheet's associated workbook
     * @param SheetManager $sheetManager To manage sheets
     * @throws
     */
    public function __construct($sheetIndex, $associatedWorkbookId, SheetManager $sheetManager)
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
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @return string
     */
    public function getAssociatedWorkbookId()
    {
        return $this->associatedWorkbookId;
    }

    /**
     * @return string Name of the sheet
     */
    public function getName()
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
    public function setName($name)
    {
        $this->sheetManager->throwIfNameIsInvalid($name, $this);

        $this->name = $name;

        $this->sheetManager->markSheetNameAsUsed($this);

        return $this;
    }

    /**
     * @return string
     */
    public function getAutoFilter()
    {
        return $this->autoFilter;
    }

    /**
     * @param string $range
     */
    public function setAutoFilter($range) {
        $this->autoFilter = $range;
    }

    /**
     * @return mixed|null
     */
    public function getMergeCells()
    {
        return $this->mergeCells;
    }

    /**
     * @param string $range Cells range
     */
    public function mergeCells($range)
    {
        $this->mergeCells[] = $range;
    }


    /**
     * @return Comment[]
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param Comment $comment
     */
    public function addComment($comment)
    {
        $this->comments[] = $comment;
    }

    /**
     * @return ColumnDimension[]|null
     */
    public function getColumnDimensions()
    {
        return $this->columnsDimensions;
    }

    /**
     * @param ColumnDimension $columnDimension
     */
    public function addColumnDimension(ColumnDimension $columnDimension)
    {
        $this->columnsDimensions[] = $columnDimension;
    }

    /**
     * @param ColumnDimension[] $dimensions
     */
    public function setColumnDimensions($dimensions)
    {
        $this->columnsDimensions = $dimensions;
    }

    /**
     * @return bool isVisible Visibility of the sheet
     */
    public function isVisible()
    {
        return $this->isVisible;
    }

    /**
     * @param bool $isVisible Visibility of the sheet
     * @return Sheet
     */
    public function setIsVisible($isVisible)
    {
        $this->isVisible = $isVisible;

        return $this;
    }

    /**
     * Calculate widths for auto-size columns
     *
     * @param int[] $columnMaxLengths
     * @param Style $defaultStyle
     * @return Sheet;
     */
    public function calculateColumnWidths($columnMaxLengths, ?Style $defaultStyle = null)
    {

        foreach ($this->getColumnDimensions() as $colDimension) {
            if ($colDimension->getAutoSize() && isset($columnMaxLengths[$colDimension->getColumnIndex()])) {
                $width = ColumnDimension::calculateColumnWidth($columnMaxLengths[$colDimension->getColumnIndex()],$defaultStyle); // tem q ver se tem style D: );
                $colDimension->setWidth($width > 0 ? $width : ColumnDimension::DEFAULT_COLUMN_WIDTH);
            }
        }

        return $this;
    }

}

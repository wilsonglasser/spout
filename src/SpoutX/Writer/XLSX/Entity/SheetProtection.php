<?php

declare(strict_types=1);

namespace SpoutX\Writer\XLSX\Entity;

use SpoutX\Writer\XLSX\Helper\PasswordHashHelper;

/**
 * Worksheet protection (<sheetProtection>): locks editing of the sheet's
 * content. This is NOT the same as hiding a sheet (see Sheet::setVisibility)
 * — it prevents changes, optionally guarded by a password.
 *
 * Mutable: set the lock flags directly or via the constructor.
 */
class SheetProtection
{
    public function __construct(
        public ?string $password = null,
        public bool $lockSheet = false,
        public bool $lockObjects = false,
        public bool $lockScenarios = false,
        public bool $lockCellFormatting = false,
        public bool $lockColumnFormatting = false,
        public bool $lockRowFormatting = false,
        public bool $lockColumnInsert = false,
        public bool $lockRowInsert = false,
        public bool $lockColumnDelete = false,
        public bool $lockRowDelete = false,
        public bool $lockLockedCellSelection = false,
        public bool $lockUnlockedCellsSelection = false,
        public bool $lockAutoFilter = false,
        public bool $lockSort = false,
        public bool $lockHyperlinkInsert = false,
        public bool $lockPivotTables = false,
    ) {
    }

    public function getXml(): string
    {
        $attributes = [
            'password' => $this->password !== null ? PasswordHashHelper::make($this->password) : '',
            'sheet' => $this->lockSheet,
            'objects' => $this->lockObjects,
            'scenarios' => $this->lockScenarios,
            'formatCells' => $this->lockCellFormatting,
            'formatColumns' => $this->lockColumnFormatting,
            'formatRows' => $this->lockRowFormatting,
            'insertColumns' => $this->lockColumnInsert,
            'insertRows' => $this->lockRowInsert,
            'deleteColumns' => $this->lockColumnDelete,
            'deleteRows' => $this->lockRowDelete,
            'selectLockedCells' => $this->lockLockedCellSelection,
            'selectUnlockedCells' => $this->lockUnlockedCellsSelection,
            'autoFilter' => $this->lockAutoFilter,
            'sort' => $this->lockSort,
            'hyperlink' => $this->lockHyperlinkInsert,
            'pivotTables' => $this->lockPivotTables,
        ];

        $xml = '<sheetProtection';
        foreach ($attributes as $name => $value) {
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }
            $xml .= ' ' . $name . '="' . $value . '"';
        }

        return $xml . '/>';
    }
}

<?php

declare(strict_types=1);

namespace SpoutX\Writer\XLSX\Entity;

use SpoutX\Writer\Common\Helper\CellHelper;

/**
 * A worksheet <sheetView>: controls gridlines, zoom, RTL, the top-left cell and
 * freeze panes. Mutable — set properties directly or via the fluent helpers.
 *
 * To freeze the first (header) row: setFreezeRow(2).
 * To freeze the first column: setFreezeColumn('B').
 */
class SheetView
{
    public function __construct(
        public bool $showFormulas = false,
        public bool $showGridLines = true,
        public bool $showRowColHeaders = true,
        public bool $showZeros = true,
        public bool $rightToLeft = false,
        public bool $tabSelected = false,
        public bool $showOutlineSymbols = true,
        public bool $defaultGridColor = true,
        public string $view = 'normal',
        public string $topLeftCell = 'A1',
        public int $colorId = 64,
        public int $zoomScale = 100,
        public int $zoomScaleNormal = 100,
        public int $zoomScalePageLayoutView = 100,
        public int $workbookViewId = 0,
        public int $freezeRow = 0,
        public string $freezeColumn = 'A',
    ) {
    }

    /**
     * Freeze rows above the given (1-based) row. Set to 2 to freeze the first row.
     */
    public function setFreezeRow(int $freezeRow): self
    {
        $this->freezeRow = $freezeRow;

        return $this;
    }

    /**
     * Freeze columns left of the given column. Set to 'B' to freeze the first column.
     */
    public function setFreezeColumn(string $freezeColumn): self
    {
        $this->freezeColumn = $freezeColumn;

        return $this;
    }

    public function setZoomScale(int $zoomScale): self
    {
        $this->zoomScale = $zoomScale;

        return $this;
    }

    public function setShowGridLines(bool $showGridLines): self
    {
        $this->showGridLines = $showGridLines;

        return $this;
    }

    public function setRightToLeft(bool $rightToLeft): self
    {
        $this->rightToLeft = $rightToLeft;

        return $this;
    }

    public function getXml(): string
    {
        return '<sheetView' . $this->getSheetViewAttributes() . '>'
            . $this->getFreezeCellPaneXml()
            . '</sheetView>';
    }

    private function getSheetViewAttributes(): string
    {
        return $this->generateAttributes([
            'showFormulas' => $this->showFormulas,
            'showGridLines' => $this->showGridLines,
            'showRowColHeaders' => $this->showRowColHeaders,
            'showZeros' => $this->showZeros,
            'rightToLeft' => $this->rightToLeft,
            'tabSelected' => $this->tabSelected,
            'showOutlineSymbols' => $this->showOutlineSymbols,
            'defaultGridColor' => $this->defaultGridColor,
            'view' => $this->view,
            'topLeftCell' => $this->topLeftCell,
            'colorId' => $this->colorId,
            'zoomScale' => $this->zoomScale,
            'zoomScaleNormal' => $this->zoomScaleNormal,
            'zoomScalePageLayoutView' => $this->zoomScalePageLayoutView,
            'workbookViewId' => $this->workbookViewId,
        ]);
    }

    private function getFreezeCellPaneXml(): string
    {
        if ($this->freezeRow < 2 && $this->freezeColumn === 'A') {
            return '';
        }

        // Normalize to a minimum row of 1 so a column-only freeze (freezeRow left at
        // its default 0) still yields a valid ySplit >= 0 and a valid topLeftCell.
        $effectiveRow = max(1, $this->freezeRow);
        $columnIndex = CellHelper::getColumnToIndexFromCellIndex($this->freezeColumn . '1');

        return '<pane' . $this->generateAttributes([
            'xSplit' => $columnIndex,
            'ySplit' => $effectiveRow - 1,
            'topLeftCell' => $this->freezeColumn . $effectiveRow,
            'activePane' => 'bottomRight',
            'state' => 'frozen',
        ]) . '/>';
    }

    /**
     * @param array<string, bool|int|string> $data attribute name => value
     */
    private function generateAttributes(array $data): string
    {
        $attributes = array_map(static function (string $key, bool|int|string $value): string {
            if (\is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }

            return $key . '="' . $value . '"';
        }, array_keys($data), $data);

        return ' ' . implode(' ', $attributes);
    }
}

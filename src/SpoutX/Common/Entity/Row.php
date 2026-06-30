<?php

declare(strict_types=1);

namespace SpoutX\Common\Entity;

use SpoutX\Common\Entity\Style\Style;

class Row
{
    /**
     * The cells in this row
     * @var Cell[]
     */
    protected array $cells = [];

    /** The row style */
    protected Style $style;

    /**
     * @param Cell[] $cells
     */
    public function __construct(array $cells, ?Style $style = null)
    {
        $this
            ->setCells($cells)
            ->setStyle($style);
    }

    /**
     * @return Cell[]
     */
    public function getCells(): array
    {
        return $this->cells;
    }

    /**
     * @param Cell[] $cells
     */
    public function setCells(array $cells): self
    {
        $this->cells = [];
        foreach ($cells as $cell) {
            $this->addCell($cell);
        }

        return $this;
    }

    public function setCellAtIndex(Cell $cell, int $cellIndex): self
    {
        $this->cells[$cellIndex] = $cell;

        return $this;
    }

    public function getCellAtIndex(int $cellIndex): ?Cell
    {
        return $this->cells[$cellIndex] ?? null;
    }

    public function addCell(Cell $cell): self
    {
        $this->cells[] = $cell;

        return $this;
    }

    public function getNumCells(): int
    {
        return count($this->cells);
    }

    public function getStyle(): Style
    {
        return $this->style;
    }

    public function setStyle(?Style $style): self
    {
        $this->style = $style ?? new Style();

        return $this;
    }

    /**
     * @return array The row values, as array
     */
    public function toArray(): array
    {
        return array_map(static fn (Cell $cell) => $cell->getValue(), $this->cells);
    }
}

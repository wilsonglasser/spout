<?php

declare(strict_types=1);

namespace SpoutX\Writer\Common\Manager;

use SpoutX\Common\Entity\Cell;
use SpoutX\Common\Entity\Style\Style;
use SpoutX\Writer\Common\Manager\Style\StyleMerger;

class CellManager
{
    protected StyleMerger $styleMerger;

    public function __construct(StyleMerger $styleMerger)
    {
        $this->styleMerger = $styleMerger;
    }

    /**
     * Merges a Style into a cell's Style.
     */
    public function applyStyle(Cell $cell, Style $style): void
    {
        $mergedStyle = $this->styleMerger->merge($cell->getStyle(), $style);
        $cell->setStyle($mergedStyle);
    }
}

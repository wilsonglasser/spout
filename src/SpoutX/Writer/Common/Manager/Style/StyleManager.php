<?php

declare(strict_types=1);

namespace SpoutX\Writer\Common\Manager\Style;

use SpoutX\Common\Entity\Cell;
use SpoutX\Common\Entity\CellType;
use SpoutX\Common\Entity\Style\Style;

/**
 * Class StyleManager
 * Manages styles to be applied to a cell
 */
class StyleManager implements StyleManagerInterface
{
    /** @var StyleRegistry Registry for all used styles */
    protected $styleRegistry;

    public function __construct(StyleRegistry $styleRegistry)
    {
        $this->styleRegistry = $styleRegistry;
    }

    /**
     * Returns the default style
     *
     * @return Style Default style
     */
    protected function getDefaultStyle(): Style
    {
        // By construction, the default style has ID 0
        return $this->styleRegistry->getRegisteredStyles()[0];
    }

    /**
     * Registers the given style as a used style.
     * Duplicate styles won't be registered more than once.
     *
     * @param Style $style The style to be registered
     * @return Style The registered style, updated with an internal ID.
     */
    public function registerStyle(Style $style): Style
    {
        return $this->styleRegistry->registerStyle($style);
    }

    /**
     * Apply additional styles if the given row needs it.
     * Typically, set "wrap text" if a cell contains a new line.
     *
     * @return Style
     */
    public function applyExtraStylesIfNeeded(Cell|array $cell)
    {
        $updatedStyle = $this->applyWrapTextIfCellContainsNewLine($cell);

        return $updatedStyle;
    }

    /**
     * Set the "wrap text" option if a cell of the given row contains a new line.
     *
     * @NOTE: There is a bug on the Mac version of Excel (2011 and below) where new lines
     *        are ignored even when the "wrap text" option is set. This only occurs with
     *        inline strings (shared strings do work fine).
     *        A workaround would be to encode "\n" as "_x000D_" but it does not work
     *        on the Windows version of Excel...
     *
     * @param Cell|array $cell The cell the style should be applied to
     * @return \SpoutX\Common\Entity\Style\Style The eventually updated style
     */
    protected function applyWrapTextIfCellContainsNewLine(Cell|array $cell)
    {
        if ($cell instanceof Cell) {
            $cellStyle = $cell->getStyle();
            $value = $cell->isString() || $cell->isText() ? $cell->getValue() : null;
        } else {
            $cellStyle = isset($cell[2]) ? $cell[2] : null;
            $value = $cell[0] === CellType::String || $cell[0] === CellType::Text ? $cell[1] : null;
        }

        // if the "wrap text" option is already set, no-op
        if ($cellStyle) {
            if ($cellStyle->hasSetWrapText()) {
                return $cellStyle;
            }

            if ($value !== null && strpos((string) $value, "\n") !== false) {
                $cellStyle->setShouldWrapText();
            }
        }

        return $cellStyle;
    }
}

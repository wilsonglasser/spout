<?php

namespace WilsonGlasser\Spout\Writer\Common\Manager\Style;

use WilsonGlasser\Spout\Common\Entity\Cell;
use WilsonGlasser\Spout\Common\Entity\Style\Style;

/**
 * Class StyleManager
 * Manages styles to be applied to a cell
 */
class StyleManager implements StyleManagerInterface
{
    /** @var StyleRegistry Registry for all used styles */
    protected $styleRegistry;

    /**
     * @param StyleRegistry $styleRegistry
     */
    public function __construct(StyleRegistry $styleRegistry)
    {
        $this->styleRegistry = $styleRegistry;
    }

    /**
     * Returns the default style
     *
     * @return Style Default style
     */
    protected function getDefaultStyle()
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
    public function registerStyle($style)
    {
        return $this->styleRegistry->registerStyle($style);
    }

    /**
     * Apply additional styles if the given row needs it.
     * Typically, set "wrap text" if a cell contains a new line.
     *
     * @param Cell|array $cell
     * @return PossiblyUpdatedStyle The eventually updated style
     */
    public function applyExtraStylesIfNeeded($cell) : PossiblyUpdatedStyle
    {
        return $this->applyWrapTextIfCellContainsNewLine($cell);
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
     * @return PossiblyUpdatedStyle The eventually updated style
     */
    protected function applyWrapTextIfCellContainsNewLine($cell) : PossiblyUpdatedStyle
    {
        if ($cell instanceof Cell) {
            $cellStyle = $cell->getStyle();
            $value = $cell->isString() ? $cell->getValue() : null;
        } else {
            $cellStyle = isset($cell[2]) ? $cell[2] : new Style();
            $value = $cell[0] === Cell::TYPE_STRING ? $cell[1] : null;
        }

        // if the "wrap text" option is already set, no-op
        if ($cellStyle->hasSetWrapText()) {
            return new PossiblyUpdatedStyle($cellStyle, false);
        }

        if ($value !== null && strpos((string) $value, "\n") !== false) {
            $cellStyle->setShouldWrapText();
            return new PossiblyUpdatedStyle($cellStyle, true);
        }

        return new PossiblyUpdatedStyle($cellStyle, false);
    }
}

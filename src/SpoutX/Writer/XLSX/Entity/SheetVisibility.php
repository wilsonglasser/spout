<?php

declare(strict_types=1);

namespace SpoutX\Writer\XLSX\Entity;

/**
 * Worksheet tab visibility (the "state" attribute of <sheet> in workbook.xml).
 *
 * - Visible:    shown normally.
 * - Hidden:     hidden, but a user can unhide it from the Excel UI.
 * - VeryHidden: hidden and NOT unhideable from the UI (only via code/VBA).
 *
 * Note: visibility is independent of {@see SheetProtection} (which locks editing)
 * and of workbook structure protection (which prevents unhiding).
 */
enum SheetVisibility: string
{
    case Visible = 'visible';
    case Hidden = 'hidden';
    case VeryHidden = 'veryHidden';
}

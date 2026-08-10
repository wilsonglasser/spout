<?php

declare(strict_types=1);

namespace SpoutX\Writer\Common\Helper;

use SpoutX\Common\Entity\Style\Style;

/**
 * Class CellHelper
 * This class provides helper functions when working with cells
 */
class CellHelper
{
    /**
     * How wide is a default column for a given default font and size?
     * Empirical data found by inspecting real Excel files and reading off the pixel width
     * in Microsoft Office Excel 2007.
     */
    public static array $defaultColumnWidths = [
        'Arial' => [
            1 => ['px' => 24, 'width' => 12.00000000],
            2 => ['px' => 24, 'width' => 12.00000000],
            3 => ['px' => 32, 'width' => 10.66406250],
            4 => ['px' => 32, 'width' => 10.66406250],
            5 => ['px' => 40, 'width' => 10.00000000],
            6 => ['px' => 48, 'width' =>  9.59765625],
            7 => ['px' => 48, 'width' =>  9.59765625],
            8 => ['px' => 56, 'width' =>  9.33203125],
            9 => ['px' => 64, 'width' =>  9.14062500],
            10 => ['px' => 64, 'width' =>  9.14062500],
        ],
        'Calibri' => [
            1 => ['px' => 24, 'width' => 12.00000000],
            2 => ['px' => 24, 'width' => 12.00000000],
            3 => ['px' => 32, 'width' => 10.66406250],
            4 => ['px' => 32, 'width' => 10.66406250],
            5 => ['px' => 40, 'width' => 10.00000000],
            6 => ['px' => 48, 'width' =>  9.59765625],
            7 => ['px' => 48, 'width' =>  9.59765625],
            8 => ['px' => 56, 'width' =>  9.33203125],
            9 => ['px' => 56, 'width' =>  9.33203125],
            10 => ['px' => 64, 'width' =>  9.14062500],
            11 => ['px' => 64, 'width' =>  9.14062500],
        ],
        'Verdana' => [
            1 => ['px' => 24, 'width' => 12.00000000],
            2 => ['px' => 24, 'width' => 12.00000000],
            3 => ['px' => 32, 'width' => 10.66406250],
            4 => ['px' => 32, 'width' => 10.66406250],
            5 => ['px' => 40, 'width' => 10.00000000],
            6 => ['px' => 48, 'width' =>  9.59765625],
            7 => ['px' => 48, 'width' =>  9.59765625],
            8 => ['px' => 64, 'width' =>  9.14062500],
            9 => ['px' => 72, 'width' =>  9.00000000],
            10 => ['px' => 72, 'width' =>  9.00000000],
        ],
    ];

    /** @var array Cache containing the mapping column index => cell index */
    private static array $columnIndexToCellIndexCache = [];

    /**
     * Returns the cell index (base 26) associated to the base 10 column index.
     * Excel uses A to Z letters for column indexing, where A is the 1st column,
     * Z is the 26th and AA is the 27th.
     * The mapping is zero based, so that 0 maps to A, B maps to 1, Z to 25 and AA to 26.
     *
     * @param int $columnIndex The Excel column index (0, 42, ...)
     * @return string The associated cell index ('A', 'BC', ...)
     */
    public static function getCellIndexFromColumnIndex(int $columnIndex): string
    {
        $originalColumnIndex = $columnIndex;

        // Using isset here because it is way faster than array_key_exists...
        if (!isset(self::$columnIndexToCellIndexCache[$originalColumnIndex])) {
            $cellIndex = '';
            $capitalAAsciiValue = ord('A');

            do {
                $modulus = $columnIndex % 26;
                $cellIndex = chr($capitalAAsciiValue + $modulus) . $cellIndex;

                // substracting 1 because it's zero-based
                $columnIndex = (int) ($columnIndex / 26) - 1;
            } while ($columnIndex >= 0);

            self::$columnIndexToCellIndexCache[$originalColumnIndex] = $cellIndex;
        }

        return self::$columnIndexToCellIndexCache[$originalColumnIndex];
    }


    /**
     * Returns the column index (base 10) associated to the base 26 cell index.
     * Excel uses A to Z letters for column indexing, where A is the 1st column,
     * Z is the 26th and AA is the 27th.
     * The mapping is zero based, so that 0 maps to A, B maps to 1, Z to 25 and AA to 26.
     *
     * @param string|int $columnIndex  The associated cell index ('A', 'BC', ...)
     * @return int The Excel column index (0, 42, ...)
     */
    public static function getColumnToIndexFromCellIndex(string|int $columnIndex): int
    {
        $originalColumnIndex =  preg_replace('/[0-9]+/', '', strtoupper($columnIndex));

        $capitalAAsciiValue = ord('A') - 1;

        $columnIndex = strrev($originalColumnIndex);

        $cellIndex = 0;

        for ($i = 0 ; $i < strlen($columnIndex); $i++) {
            $cellIndex += (ord(substr($columnIndex, $i, 1)) - $capitalAAsciiValue)  * pow(26, $i);
        }

        return $cellIndex - 1;
    }

    /**
     * Convert pixels to column width. Exact algorithm not known.
     * By inspection of a real Excel file using Calibri 11, one finds 1000px ~ 142.85546875
     * This gives a conversion factor of 7. Also, we assume that pixels and font size are proportional.
     *
     * @param     int $pValue    Value in pixels
     * @param     Style $pDefaultStyle    Default style
     * @return     float          Value in cell dimension
     */
    public static function pixelsToCellDimension(int $pValue, Style $pDefaultStyle): float
    {
        // Font name and size
        $name = $pDefaultStyle->getFontName();
        $size = $pDefaultStyle->getFontSize();

        if (isset(self::$defaultColumnWidths[$name][$size])) {
            // Exact width can be determined
            $colWidth = $pValue * self::$defaultColumnWidths[$name][$size]['width'] / self::$defaultColumnWidths[$name][$size]['px'];
        } else {
            // We don't have data for this particular font and size, use approximation by
            // extrapolating from Calibri 11
            $colWidth = $pValue * 11 * self::$defaultColumnWidths['Calibri'][11]['width'] / self::$defaultColumnWidths['Calibri'][11]['px'] / $size;
        }

        return $colWidth;
    }

    /**
     * Checks if a coordinate represents a range of cells.
     *
     * @param string $coord eg: 'A1' or 'A1:A2' or 'A1:A2,C1:C2'
     *
     * @return bool Whether the coordinate represents a range of cells
     */
    public static function coordinateIsRange(string $coord): bool
    {
        return (strpos($coord, ':') !== false) || (strpos($coord, ',') !== false);
    }

    /**
     * Coordinate from string.
     *
     * @param string $pCoordinateString eg: 'A1'
     *
     * @throws Exception
     *
     * @return string[] Array containing column and row (indexes 0 and 1)
     */
    public static function coordinateFromString(string $pCoordinateString): array
    {
        if (preg_match('/^([$]?[A-Z]{1,3})([$]?\\d{1,7})$/', $pCoordinateString, $matches)) {
            return [$matches[1], $matches[2]];
        } elseif (self::coordinateIsRange($pCoordinateString)) {
            throw new \Exception('Cell coordinate string can not be a range of cells');
        } elseif ($pCoordinateString == '') {
            throw new \Exception('Cell coordinate can not be zero-length string');
        }

        throw new \Exception('Invalid cell coordinate ' . $pCoordinateString);
    }
}

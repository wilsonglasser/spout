<?php

declare(strict_types=1);

namespace SpoutX\Writer\XLSX\Helper;

use SpoutX\Common\Entity\Style\BorderPart;

class BorderHelper
{
    /**
     * Maps a border style + width (by their backing value) to the XLSX line style.
     *
     * @var array<string, array<string, string>>
     */
    public static array $xlsxStyleMap = [
        'solid' => [
            'thin' => 'thin',
            'medium' => 'medium',
            'thick' => 'thick',
        ],
        'dotted' => [
            'thin' => 'dotted',
            'medium' => 'dotted',
            'thick' => 'dotted',
        ],
        'dashed' => [
            'thin' => 'dashed',
            'medium' => 'mediumDashed',
            'thick' => 'mediumDashed',
        ],
        'double' => [
            'thin' => 'double',
            'medium' => 'double',
            'thick' => 'double',
        ],
        'none' => [
            'thin' => 'none',
            'medium' => 'none',
            'thick' => 'none',
        ],
    ];

    public static function serializeBorderPart(BorderPart $borderPart): string
    {
        $borderStyle = self::getBorderStyle($borderPart);
        $name = $borderPart->getName()->value;

        $colorEl = $borderPart->getColor() ? sprintf('<color rgb="%s"/>', $borderPart->getColor()) : '';
        $partEl = sprintf(
            '<%s style="%s">%s</%s>',
            $name,
            $borderStyle,
            $colorEl,
            $name
        );

        return $partEl . PHP_EOL;
    }

    /**
     * Get the style definition from the style map.
     */
    protected static function getBorderStyle(BorderPart $borderPart): string
    {
        return self::$xlsxStyleMap[$borderPart->getStyle()->value][$borderPart->getWidth()->value];
    }
}

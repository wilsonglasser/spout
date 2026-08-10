<?php

declare(strict_types=1);

namespace SpoutX\Writer\Common\Creator;

use SpoutX\Common\Entity\Cell;
use SpoutX\Common\Entity\Row;
use SpoutX\Common\Entity\Style\Style;
use SpoutX\Writer\WriterInterface;

/**
 * Class WriterEntityFactory
 * Factory to create external entities
 */
class WriterEntityFactory
{
    /**
     * This creates an instance of the appropriate writer, given the type of the file to be written
     *
     * @param  string $writerType Type of the writer to instantiate
     * @throws \SpoutX\Common\Exception\UnsupportedTypeException
     * @return WriterInterface
     */
    public static function createWriter(string $writerType): WriterInterface
    {
        return (new WriterFactory())->create($writerType);
    }

    /**
     * @param Cell[] $cells
     */
    public static function createRow(array $cells = [], ?Style $rowStyle = null): Row
    {
        return new Row($cells, $rowStyle);
    }

    public static function createRowFromArray(array $cellValues = [], ?Style $rowStyle = null): Row
    {
        $cells = array_map(function ($cellValue) {
            return new Cell($cellValue);
        }, $cellValues);

        return new Row($cells, $rowStyle);
    }

    public static function createCell(mixed $cellValue, ?Style $cellStyle = null): Cell
    {
        return new Cell($cellValue, $cellStyle);
    }
}

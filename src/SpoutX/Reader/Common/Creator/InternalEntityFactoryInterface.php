<?php

declare(strict_types=1);

namespace SpoutX\Reader\Common\Creator;

use SpoutX\Common\Entity\Cell;
use SpoutX\Common\Entity\Row;

/**
 * Interface EntityFactoryInterface
 */
interface InternalEntityFactoryInterface
{
    /**
     * @param Cell[] $cells
     * @return Row
     */
    public function createRow(array $cells = []): Row;

    /**
     * @param mixed $cellValue
     * @return Cell
     */
    public function createCell(mixed $cellValue): Cell;
}

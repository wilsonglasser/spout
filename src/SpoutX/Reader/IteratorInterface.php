<?php

declare(strict_types=1);

namespace SpoutX\Reader;

/**
 * Interface IteratorInterface
 */
interface IteratorInterface extends \Iterator
{
    /**
     * Cleans up what was created to iterate over the object.
     */
    public function end(): void;
}

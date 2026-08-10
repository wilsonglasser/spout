<?php

declare(strict_types=1);

namespace SpoutX\Common\Manager;

/**
 * Interface OptionsManagerInterface
 */
interface OptionsManagerInterface
{
    public function setOption(string $optionName, mixed $optionValue): void;

    /**
     * @return mixed|null The set option or NULL if no option with given name found
     */
    public function getOption(string $optionName): mixed;
}

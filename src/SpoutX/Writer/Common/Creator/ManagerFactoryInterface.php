<?php

declare(strict_types=1);

namespace SpoutX\Writer\Common\Creator;

use SpoutX\Common\Manager\OptionsManagerInterface;
use SpoutX\Writer\Common\Manager\SheetManager;
use SpoutX\Writer\Common\Manager\WorkbookManagerInterface;

/**
 * Interface ManagerFactoryInterface
 */
interface ManagerFactoryInterface
{
    public function createWorkbookManager(OptionsManagerInterface $optionsManager): WorkbookManagerInterface;

    public function createSheetManager(): SheetManager;
}

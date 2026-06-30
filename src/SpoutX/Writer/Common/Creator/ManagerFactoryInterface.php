<?php

namespace SpoutX\Writer\Common\Creator;

use SpoutX\Common\Manager\OptionsManagerInterface;
use SpoutX\Writer\Common\Manager\SheetManager;
use SpoutX\Writer\Common\Manager\WorkbookManagerInterface;

/**
 * Interface ManagerFactoryInterface
 */
interface ManagerFactoryInterface
{
    /**
     * @param OptionsManagerInterface $optionsManager
     * @return WorkbookManagerInterface
     */
    public function createWorkbookManager(OptionsManagerInterface $optionsManager);

    /**
     * @return SheetManager
     */
    public function createSheetManager();
}

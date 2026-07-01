<?php

declare(strict_types=1);

namespace SpoutX\Reader\XLSX\Creator;

use SpoutX\Common\Manager\OptionsManagerInterface;
use SpoutX\Reader\Common\Manager\RowManager;
use SpoutX\Reader\XLSX\Manager\SharedStringsCaching\CachingStrategyFactory;
use SpoutX\Reader\XLSX\Manager\SharedStringsManager;
use SpoutX\Reader\XLSX\Manager\SheetManager;
use SpoutX\Reader\XLSX\Manager\StyleManager;
use SpoutX\Reader\XLSX\Manager\WorkbookRelationshipsManager;

/**
 * Class ManagerFactory
 * Factory to create managers
 */
class ManagerFactory
{
    /** @var HelperFactory */
    private HelperFactory $helperFactory;

    /** @var CachingStrategyFactory */
    private CachingStrategyFactory $cachingStrategyFactory;

    /** @var WorkbookRelationshipsManager */
    private WorkbookRelationshipsManager $cachedWorkbookRelationshipsManager;

    /**
     * @param HelperFactory $helperFactory Factory to create helpers
     * @param CachingStrategyFactory $cachingStrategyFactory Factory to create shared strings caching strategies
     */
    public function __construct(HelperFactory $helperFactory, CachingStrategyFactory $cachingStrategyFactory)
    {
        $this->helperFactory = $helperFactory;
        $this->cachingStrategyFactory = $cachingStrategyFactory;
    }

    /**
     * @param string $filePath Path of the XLSX file being read
     * @param string $tempFolder Temporary folder where the temporary files to store shared strings will be stored
     * @param InternalEntityFactory $entityFactory Factory to create entities
     * @return SharedStringsManager
     */
    public function createSharedStringsManager(string $filePath, string $tempFolder, InternalEntityFactory $entityFactory): SharedStringsManager
    {
        $workbookRelationshipsManager = $this->createWorkbookRelationshipsManager($filePath, $entityFactory);

        return new SharedStringsManager(
            $filePath,
            $tempFolder,
            $workbookRelationshipsManager,
            $entityFactory,
            $this->helperFactory,
            $this->cachingStrategyFactory
        );
    }

    /**
     * @param string $filePath Path of the XLSX file being read
     * @param InternalEntityFactory $entityFactory Factory to create entities
     * @return WorkbookRelationshipsManager
     */
    private function createWorkbookRelationshipsManager(string $filePath, InternalEntityFactory $entityFactory): WorkbookRelationshipsManager
    {
        if (!isset($this->cachedWorkbookRelationshipsManager)) {
            $this->cachedWorkbookRelationshipsManager = new WorkbookRelationshipsManager($filePath, $entityFactory);
        }

        return $this->cachedWorkbookRelationshipsManager;
    }

    /**
     * @param string $filePath Path of the XLSX file being read
     * @param \SpoutX\Common\Manager\OptionsManagerInterface $optionsManager Reader's options manager
     * @param \SpoutX\Reader\XLSX\Manager\SharedStringsManager $sharedStringsManager Manages shared strings
     * @param InternalEntityFactory $entityFactory Factory to create entities
     * @return SheetManager
     */
    public function createSheetManager(string $filePath, OptionsManagerInterface $optionsManager, SharedStringsManager $sharedStringsManager, InternalEntityFactory $entityFactory): SheetManager
    {
        $escaper = $this->helperFactory->createStringsEscaper();

        return new SheetManager($filePath, $optionsManager, $sharedStringsManager, $escaper, $entityFactory);
    }

    /**
     * @param string $filePath Path of the XLSX file being read
     * @param InternalEntityFactory $entityFactory Factory to create entities
     * @return StyleManager
     */
    public function createStyleManager(string $filePath, InternalEntityFactory $entityFactory): StyleManager
    {
        $workbookRelationshipsManager = $this->createWorkbookRelationshipsManager($filePath, $entityFactory);

        return new StyleManager($filePath, $workbookRelationshipsManager, $entityFactory);
    }

    /**
     * @param InternalEntityFactory $entityFactory Factory to create entities
     * @return RowManager
     */
    public function createRowManager(InternalEntityFactory $entityFactory): RowManager
    {
        return new RowManager($entityFactory);
    }
}

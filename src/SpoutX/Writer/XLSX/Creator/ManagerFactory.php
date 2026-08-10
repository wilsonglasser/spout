<?php

declare(strict_types=1);

namespace SpoutX\Writer\XLSX\Creator;

use SpoutX\Common\Manager\OptionsManagerInterface;
use SpoutX\Writer\Common\Creator\InternalEntityFactory;
use SpoutX\Writer\Common\Creator\ManagerFactoryInterface;
use SpoutX\Writer\Common\Entity\Options;
use SpoutX\Writer\Common\Manager\RowManager;
use SpoutX\Writer\Common\Manager\SheetManager;
use SpoutX\Writer\Common\Manager\Style\StyleMerger;
use SpoutX\Writer\Common\Manager\WorkbookManagerInterface;
use SpoutX\Writer\XLSX\Manager\Comment\CommentManager;
use SpoutX\Writer\XLSX\Manager\SharedStringsManager;
use SpoutX\Writer\XLSX\Manager\Style\StyleManager;
use SpoutX\Writer\XLSX\Manager\Style\StyleRegistry;
use SpoutX\Writer\XLSX\Manager\WorkbookManager;
use SpoutX\Writer\XLSX\Manager\WorksheetManager;

/**
 * Class ManagerFactory
 * Factory for managers needed by the XLSX Writer
 */
class ManagerFactory implements ManagerFactoryInterface
{
    /** @var InternalEntityFactory */
    protected InternalEntityFactory $entityFactory;

    /** @var HelperFactory $helperFactory */
    protected HelperFactory $helperFactory;

    public function __construct(InternalEntityFactory $entityFactory, HelperFactory $helperFactory)
    {
        $this->entityFactory = $entityFactory;
        $this->helperFactory = $helperFactory;
    }

    /**
     * @return WorkbookManager
     */
    public function createWorkbookManager(OptionsManagerInterface $optionsManager): WorkbookManagerInterface
    {
        $workbook = $this->entityFactory->createWorkbook();

        $fileSystemHelper = $this->helperFactory->createSpecificFileSystemHelper($optionsManager, $this->entityFactory);
        $fileSystemHelper->createBaseFilesAndFolders();

        $xlFolder = $fileSystemHelper->getXlFolder();
        $sharedStringsManager = $this->createSharedStringsManager($xlFolder);

        $styleMerger = $this->createStyleMerger();
        $styleManager = $this->createStyleManager($optionsManager);
        $worksheetManager = $this->createWorksheetManager($optionsManager, $styleManager, $styleMerger, $sharedStringsManager);

        $stringsEscaper = $this->helperFactory->createStringsEscaper();
        $commentsManager = $this->createCommentsManager($stringsEscaper);

        return new WorkbookManager(
            $workbook,
            $optionsManager,
            $worksheetManager,
            $commentsManager,
            $styleManager,
            $styleMerger,
            $fileSystemHelper,
            $this->entityFactory,
            $this
        );
    }

    private function createWorksheetManager(
        OptionsManagerInterface $optionsManager,
        StyleManager $styleManager,
        StyleMerger $styleMerger,
        SharedStringsManager $sharedStringsManager
    ): WorksheetManager {
        $rowManager = $this->createRowManager();
        $stringsEscaper = $this->helperFactory->createStringsEscaper();

        return new WorksheetManager(
            $optionsManager,
            $rowManager,
            $styleManager,
            $styleMerger,
            $sharedStringsManager,
            $stringsEscaper,
            $this->entityFactory
        );
    }

    public function createSheetManager(): SheetManager
    {
        return new SheetManager();
    }


    public function createCommentsManager($stringsEscaper): CommentManager
    {
        return new CommentManager($stringsEscaper);
    }

    public function createRowManager(): RowManager
    {
        return new RowManager();
    }

    private function createStyleManager(OptionsManagerInterface $optionsManager): StyleManager
    {
        $styleRegistry = $this->createStyleRegistry($optionsManager);

        return new StyleManager($styleRegistry);
    }

    private function createStyleRegistry(OptionsManagerInterface $optionsManager): StyleRegistry
    {
        $defaultRowStyle = $optionsManager->getOption(Options::DEFAULT_ROW_STYLE);

        return new StyleRegistry($defaultRowStyle);
    }

    private function createStyleMerger(): StyleMerger
    {
        return new StyleMerger();
    }

    /**
     * @param string $xlFolder Path to the "xl" folder
     */
    private function createSharedStringsManager(string $xlFolder): SharedStringsManager
    {
        $stringEscaper = $this->helperFactory->createStringsEscaper();

        return new SharedStringsManager($xlFolder, $stringEscaper);
    }
}

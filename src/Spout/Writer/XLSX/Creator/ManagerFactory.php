<?php

namespace WilsonGlasser\Spout\Writer\XLSX\Creator;

use WilsonGlasser\Spout\Common\Manager\OptionsManagerInterface;
use WilsonGlasser\Spout\Writer\Common\Creator\InternalEntityFactory;
use WilsonGlasser\Spout\Writer\Common\Creator\ManagerFactoryInterface;
use WilsonGlasser\Spout\Writer\Common\Entity\Options;
use WilsonGlasser\Spout\Writer\Common\Manager\RowManager;
use WilsonGlasser\Spout\Writer\Common\Manager\SheetManager;
use WilsonGlasser\Spout\Writer\Common\Manager\Style\StyleMerger;
use WilsonGlasser\Spout\Writer\XLSX\Manager\Comment\CommentManager;
use WilsonGlasser\Spout\Writer\XLSX\Manager\SharedStringsManager;
use WilsonGlasser\Spout\Writer\XLSX\Manager\Style\StyleManager;
use WilsonGlasser\Spout\Writer\XLSX\Manager\Style\StyleRegistry;
use WilsonGlasser\Spout\Writer\XLSX\Manager\WorkbookManager;
use WilsonGlasser\Spout\Writer\XLSX\Manager\WorksheetManager;

/**
 * Class ManagerFactory
 * Factory for managers needed by the XLSX Writer
 */
class ManagerFactory implements ManagerFactoryInterface
{
    /** @var InternalEntityFactory */
    protected $entityFactory;

    /** @var HelperFactory */
    protected $helperFactory;

    /**
     * @param InternalEntityFactory $entityFactory
     * @param HelperFactory $helperFactory
     */
    public function __construct(InternalEntityFactory $entityFactory, HelperFactory $helperFactory)
    {
        $this->entityFactory = $entityFactory;
        $this->helperFactory = $helperFactory;
    }

    /**
     * @param OptionsManagerInterface $optionsManager
     * @return WorkbookManager
     */
    public function createWorkbookManager(OptionsManagerInterface $optionsManager)
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

    /**
     * @param OptionsManagerInterface $optionsManager
     * @param Stylemanager $styleManager
     * @param StyleMerger $styleMerger
     * @param SharedStringsManager $sharedStringsManager
     * @return WorksheetManager
     */
    private function createWorksheetManager(
        OptionsManagerInterface $optionsManager,
        StyleManager $styleManager,
        StyleMerger $styleMerger,
        SharedStringsManager $sharedStringsManager
    ) {
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

    /**
     * @return SheetManager
     */
    public function createSheetManager()
    {
        return new SheetManager();
    }


    /**
     * @return CommentManager
     */
    public function createCommentsManager($stringsEscaper)
    {
        return new CommentManager($stringsEscaper);
    }

    /**
     * @return RowManager
     */
    public function createRowManager()
    {
        return new RowManager();
    }

    /**
     * @param OptionsManagerInterface $optionsManager
     * @return StyleManager
     */
    private function createStyleManager(OptionsManagerInterface $optionsManager)
    {
        $styleRegistry = $this->createStyleRegistry($optionsManager);

        return new StyleManager($styleRegistry);
    }

    /**
     * @param OptionsManagerInterface $optionsManager
     * @return StyleRegistry
     */
    private function createStyleRegistry(OptionsManagerInterface $optionsManager)
    {
        $defaultRowStyle = $optionsManager->getOption(Options::DEFAULT_ROW_STYLE);

        return new StyleRegistry($defaultRowStyle);
    }

    /**
     * @return StyleMerger
     */
    private function createStyleMerger()
    {
        return new StyleMerger();
    }

    /**
     * @param string $xlFolder Path to the "xl" folder
     * @return SharedStringsManager
     */
    private function createSharedStringsManager($xlFolder)
    {
        $stringEscaper = $this->helperFactory->createStringsEscaper();

        return new SharedStringsManager($xlFolder, $stringEscaper);
    }
}

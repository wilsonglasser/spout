<?php

namespace SpoutX\Writer;

use SpoutX\Common\Creator\HelperFactory;
use SpoutX\Common\Entity\Row;
use SpoutX\Common\Helper\GlobalFunctionsHelper;
use SpoutX\Common\Manager\OptionsManagerInterface;
use SpoutX\Writer\Common\Creator\ManagerFactoryInterface;
use SpoutX\Writer\Common\Entity\Options;
use SpoutX\Writer\Common\Entity\Sheet;
use SpoutX\Writer\Common\Entity\Worksheet;
use SpoutX\Writer\Common\Manager\WorkbookManagerInterface;
use SpoutX\Writer\Exception\SheetNotFoundException;
use SpoutX\Writer\Exception\WriterAlreadyOpenedException;
use SpoutX\Writer\Exception\WriterNotOpenedException;

/**
 * Class WriterMultiSheetsAbstract
 *
 * @abstract
 */
abstract class WriterMultiSheetsAbstract extends WriterAbstract
{
    /** @var ManagerFactoryInterface */
    private $managerFactory;

    /** @var WorkbookManagerInterface */
    private $workbookManager;


    /**
     * @param OptionsManagerInterface $optionsManager
     * @param GlobalFunctionsHelper $globalFunctionsHelper
     * @param HelperFactory $helperFactory
     * @param ManagerFactoryInterface $managerFactory
     */
    public function __construct(
        OptionsManagerInterface $optionsManager,
        GlobalFunctionsHelper $globalFunctionsHelper,
        HelperFactory $helperFactory,
        ManagerFactoryInterface $managerFactory
    ) {
        parent::__construct($optionsManager, $globalFunctionsHelper, $helperFactory);
        $this->managerFactory = $managerFactory;
    }

    /**
     * Sets whether new sheets should be automatically created when the max rows limit per sheet is reached.
     * This must be set before opening the writer.
     *
     * @param bool $shouldCreateNewSheetsAutomatically Whether new sheets should be automatically created when the max rows limit per sheet is reached
     * @throws WriterAlreadyOpenedException If the writer was already opened
     * @return WriterMultiSheetsAbstract
     */
    public function setShouldCreateNewSheetsAutomatically($shouldCreateNewSheetsAutomatically)
    {
        $this->throwIfWriterAlreadyOpened('Writer must be configured before opening it.');

        $this->optionsManager->setOption(Options::SHOULD_CREATE_NEW_SHEETS_AUTOMATICALLY, $shouldCreateNewSheetsAutomatically);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function openWriter()
    {
        if (!$this->workbookManager) {
            $this->workbookManager = $this->managerFactory->createWorkbookManager($this->optionsManager);
            $this->workbookManager->addNewSheetAndMakeItCurrent();
        }
    }

    /**
     * Returns all the workbook's sheets
     *
     * @throws WriterNotOpenedException If the writer has not been opened yet
     * @return Sheet[] All the workbook's sheets
     */
    public function getSheets()
    {
        $this->throwIfWorkbookIsNotAvailable();

        $externalSheets = [];
        $worksheets = $this->workbookManager->getWorksheets();

        /** @var Worksheet $worksheet */
        foreach ($worksheets as $worksheet) {
            $externalSheets[] = $worksheet->getExternalSheet();
        }

        return $externalSheets;
    }

    /**
     * Creates a new sheet and make it the current sheet. The data will now be written to this sheet.
     *
     * @throws WriterNotOpenedException If the writer has not been opened yet
     * @return Sheet The created sheet
     */
    public function addNewSheetAndMakeItCurrent()
    {
        $this->throwIfWorkbookIsNotAvailable();
        $worksheet = $this->workbookManager->addNewSheetAndMakeItCurrent();

        return $worksheet->getExternalSheet();
    }

    /**
     * Returns the current sheet
     *
     * @throws WriterNotOpenedException If the writer has not been opened yet
     * @return Sheet The current sheet
     */
    public function getCurrentSheet()
    {
        $this->throwIfWorkbookIsNotAvailable();

        return $this->workbookManager->getCurrentWorksheet()->getExternalSheet();
    }

    /**
     * Returns the current sheet
     *
     * @throws WriterNotOpenedException If the writer has not been opened yet
     * @return Worksheet The current sheet
     */
    public function getCurrentWorksheet()
    {
        $this->throwIfWorkbookIsNotAvailable();

        return $this->workbookManager->getCurrentWorksheet();
    }

    /**
     * Sets the given sheet as the current one. New data will be written to this sheet.
     * The writing will resume where it stopped (i.e. data won't be truncated).
     *
     * @param Sheet $sheet The sheet to set as current
     * @throws WriterNotOpenedException If the writer has not been opened yet
     * @throws SheetNotFoundException If the given sheet does not exist in the workbook
     * @return void
     */
    public function setCurrentSheet($sheet)
    {
        $this->throwIfWorkbookIsNotAvailable();
        $this->workbookManager->setCurrentSheet($sheet);
    }

    /**
     * Checks if the workbook has been created. Throws an exception if not created yet.
     *
     * @throws WriterNotOpenedException If the workbook is not created yet
     * @return void
     */
    protected function throwIfWorkbookIsNotAvailable()
    {
        if (!$this->workbookManager->getWorkbook()) {
            throw new WriterNotOpenedException('The writer must be opened before performing this action.');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function addRowToWriter($row)
    {
        $this->throwIfWorkbookIsNotAvailable();
        $this->workbookManager->addRowToCurrentWorksheet($row);
    }

    /**
     * {@inheritdoc}
     */
    protected function closeWriter()
    {
        if ($this->workbookManager) {
            $this->workbookManager->close($this->filePointer);
        }
    }
}

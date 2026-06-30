<?php

declare(strict_types=1);

namespace SpoutX\Writer\XLSX\Creator;

use SpoutX\Common\Helper\Escaper;
use SpoutX\Common\Manager\OptionsManagerInterface;
use SpoutX\Writer\Common\Creator\InternalEntityFactory;
use SpoutX\Writer\Common\Entity\Options;
use SpoutX\Writer\Common\Helper\ZipHelper;
use SpoutX\Writer\XLSX\Helper\FileSystemHelper;

/**
 * Class HelperFactory
 * Factory for helpers needed by the XLSX Writer
 */
class HelperFactory extends \SpoutX\Common\Creator\HelperFactory
{
    /**
     * @param OptionsManagerInterface $optionsManager
     * @param InternalEntityFactory $entityFactory
     * @return FileSystemHelper
     */
    public function createSpecificFileSystemHelper(OptionsManagerInterface $optionsManager, InternalEntityFactory $entityFactory)
    {
        $tempFolder = $optionsManager->getOption(Options::TEMP_FOLDER);
        $zipHelper = $this->createZipHelper($entityFactory);
        $escaper = $this->createStringsEscaper();

        return new FileSystemHelper($tempFolder, $zipHelper, $escaper);
    }

    /**
     * @param InternalEntityFactory $entityFactory
     * @return ZipHelper
     */
    private function createZipHelper(InternalEntityFactory $entityFactory)
    {
        return new ZipHelper($entityFactory);
    }

    /**
     * @return Escaper\XLSX
     */
    public function createStringsEscaper()
    {
        return new Escaper\XLSX();
    }

}

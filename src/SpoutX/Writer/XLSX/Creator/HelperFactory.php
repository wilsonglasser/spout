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
    public function createSpecificFileSystemHelper(OptionsManagerInterface $optionsManager, InternalEntityFactory $entityFactory): FileSystemHelper
    {
        $tempFolder = $optionsManager->getOption(Options::TEMP_FOLDER);
        $zipHelper = $this->createZipHelper($entityFactory);
        $escaper = $this->createStringsEscaper();

        return new FileSystemHelper($tempFolder, $zipHelper, $escaper);
    }

    private function createZipHelper(InternalEntityFactory $entityFactory): ZipHelper
    {
        return new ZipHelper($entityFactory);
    }

    public function createStringsEscaper(): Escaper\XLSX
    {
        return new Escaper\XLSX();
    }

}

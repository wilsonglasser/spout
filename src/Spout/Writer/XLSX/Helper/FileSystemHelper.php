<?php

namespace WilsonGlasser\Spout\Writer\XLSX\Helper;

use WilsonGlasser\Spout\Writer\Common\Entity\Worksheet;
use WilsonGlasser\Spout\Writer\Common\Helper\FileSystemWithRootFolderHelperInterface;
use WilsonGlasser\Spout\Writer\Common\Helper\ZipHelper;
use WilsonGlasser\Spout\Writer\XLSX\Manager\Comment\CommentManager;
use WilsonGlasser\Spout\Writer\XLSX\Manager\Style\StyleManager;

/**
 * Class FileSystemHelper
 * This class provides helper functions to help with the file system operations
 * like files/folders creation & deletion for XLSX files
 */
class FileSystemHelper extends \WilsonGlasser\Spout\Common\Helper\FileSystemHelper implements FileSystemWithRootFolderHelperInterface
{
    public const APP_NAME = 'Spout';

    public const RELS_FOLDER_NAME = '_rels';
    public const DOC_PROPS_FOLDER_NAME = 'docProps';
    public const XL_FOLDER_NAME = 'xl';
    public const WORKSHEETS_FOLDER_NAME = 'worksheets';

    public const RELS_FILE_NAME = '.rels';
    public const APP_XML_FILE_NAME = 'app.xml';
    public const CORE_XML_FILE_NAME = 'core.xml';
    public const CONTENT_TYPES_XML_FILE_NAME = '[Content_Types].xml';
    public const WORKBOOK_XML_FILE_NAME = 'workbook.xml';
    public const WORKBOOK_RELS_XML_FILE_NAME = 'workbook.xml.rels';
    public const STYLES_XML_FILE_NAME = 'styles.xml';

    /** @var ZipHelper Helper to perform tasks with Zip archive */
    private $zipHelper;

    /** @var \WilsonGlasser\Spout\Common\Helper\Escaper\XLSX Used to escape XML data */
    private $escaper;

    /** @var string Path to the root folder inside the temp folder where the files to create the XLSX will be stored */
    private $rootFolder;

    /** @var string Path to the "_rels" folder inside the root folder */
    private $relsFolder;

    /** @var string Path to the "docProps" folder inside the root folder */
    private $docPropsFolder;

    /** @var string Path to the "xl" folder inside the root folder */
    private $xlFolder;

    /** @var string Path to the "_rels" folder inside the "xl" folder */
    private $xlRelsFolder;

    /** @var string Path to the "worksheets" folder inside the "xl" folder */
    private $xlWorksheetsFolder;

    /** @var string Path to the "worksheets/_rels" folder inside the "xl" folder */
    private $xlWorksheetsRelsFolder;

    /**
     * @param string $baseFolderPath The path of the base folder where all the I/O can occur
     * @param ZipHelper $zipHelper Helper to perform tasks with Zip archive
     * @param \WilsonGlasser\Spout\Common\Helper\Escaper\XLSX $escaper Used to escape XML data
     */
    public function __construct($baseFolderPath, $zipHelper, $escaper)
    {
        parent::__construct($baseFolderPath);
        $this->zipHelper = $zipHelper;
        $this->escaper = $escaper;
    }

    /**
     * @return string
     */
    public function getRootFolder()
    {
        return $this->rootFolder;
    }

    /**
     * @return string
     */
    public function getXlFolder()
    {
        return $this->xlFolder;
    }

    /**
     * @return string
     */
    public function getXlWorksheetsFolder()
    {
        return $this->xlWorksheetsFolder;
    }


    /**
     * @return string
     */
    public function getXlWorksheetsRelsFolder()
    {
        return $this->xlWorksheetsRelsFolder;
    }

    /**
     * Creates all the folders needed to create a XLSX file, as well as the files that won't change.
     *
     * @throws \WilsonGlasser\Spout\Common\Exception\IOException If unable to create at least one of the base folders
     * @return void
     */
    public function createBaseFilesAndFolders()
    {
        $this
            ->createRootFolder()
            ->createRelsFolderAndFile()
            ->createDocPropsFolderAndFiles()
            ->createXlFolderAndSubFolders();
    }

    /**
     * Creates the folder that will be used as root
     *
     * @throws \WilsonGlasser\Spout\Common\Exception\IOException If unable to create the folder
     * @return FileSystemHelper
     */
    private function createRootFolder()
    {
        $this->rootFolder = $this->createFolder($this->baseFolderRealPath, \uniqid('xlsx', true));

        return $this;
    }

    /**
     * Creates the "_rels" folder under the root folder as well as the ".rels" file in it
     *
     * @throws \WilsonGlasser\Spout\Common\Exception\IOException If unable to create the folder or the ".rels" file
     * @return FileSystemHelper
     */
    private function createRelsFolderAndFile()
    {
        $this->relsFolder = $this->createFolder($this->rootFolder, self::RELS_FOLDER_NAME);

        $this->createRelsFile();

        return $this;
    }

    /**
     * Creates the ".rels" file under the "_rels" folder (under root)
     *
     * @throws \WilsonGlasser\Spout\Common\Exception\IOException If unable to create the file
     * @return FileSystemHelper
     */
    private function createRelsFile()
    {
        $relsFileContents = <<<'EOD'
<?xml version="1.0" encoding="UTF-8"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rIdWorkbook" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
    <Relationship Id="rIdCore" Type="http://schemas.openxmlformats.org/officedocument/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>
    <Relationship Id="rIdApp" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>
</Relationships>
EOD;

        $this->createFileWithContents($this->relsFolder, self::RELS_FILE_NAME, $relsFileContents);

        return $this;
    }

    /**
     * Creates the "docProps" folder under the root folder as well as the "app.xml" and "core.xml" files in it
     *
     * @throws \WilsonGlasser\Spout\Common\Exception\IOException If unable to create the folder or one of the files
     * @return FileSystemHelper
     */
    private function createDocPropsFolderAndFiles()
    {
        $this->docPropsFolder = $this->createFolder($this->rootFolder, self::DOC_PROPS_FOLDER_NAME);

        $this->createAppXmlFile();
        $this->createCoreXmlFile();

        return $this;
    }

    /**
     * Creates the "app.xml" file under the "docProps" folder
     *
     * @throws \WilsonGlasser\Spout\Common\Exception\IOException If unable to create the file
     * @return FileSystemHelper
     */
    private function createAppXmlFile()
    {
        $appName = self::APP_NAME;
        $appXmlFileContents = <<<EOD
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties">
    <Application>$appName</Application>
    <TotalTime>0</TotalTime>
</Properties>
EOD;

        $this->createFileWithContents($this->docPropsFolder, self::APP_XML_FILE_NAME, $appXmlFileContents);

        return $this;
    }

    /**
     * Creates the "themes/theme1.xml" file under the "docProps" folder
     *
     * @throws \WilsonGlasser\Spout\Common\Exception\IOException If unable to create the file
     * @return FileSystemHelper
     */
    private function createThemeXmlFile()
    {
        $appXmlFileContents = <<<EOD
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<a:theme xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" name="Office Theme"><a:themeElements><a:clrScheme name="Office"><a:dk1><a:sysClr val="windowText" lastClr="000000"/></a:dk1><a:lt1><a:sysClr val="window" lastClr="FFFFFF"/></a:lt1><a:dk2><a:srgbClr val="1F497D"/></a:dk2><a:lt2><a:srgbClr val="EEECE1"/></a:lt2><a:accent1><a:srgbClr val="4F81BD"/></a:accent1><a:accent2><a:srgbClr val="C0504D"/></a:accent2><a:accent3><a:srgbClr val="9BBB59"/></a:accent3><a:accent4><a:srgbClr val="8064A2"/></a:accent4><a:accent5><a:srgbClr val="4BACC6"/></a:accent5><a:accent6><a:srgbClr val="F79646"/></a:accent6><a:hlink><a:srgbClr val="0000FF"/></a:hlink><a:folHlink><a:srgbClr val="800080"/></a:folHlink></a:clrScheme><a:fontScheme name="Office"><a:majorFont><a:latin typeface="Cambria"/><a:ea typeface=""/><a:cs typeface=""/><a:font script="Jpan" typeface="ＭＳ Ｐゴシック"/><a:font script="Hang" typeface="맑은 고딕"/><a:font script="Hans" typeface="宋体"/><a:font script="Hant" typeface="新細明體"/><a:font script="Arab" typeface="Times New Roman"/><a:font script="Hebr" typeface="Times New Roman"/><a:font script="Thai" typeface="Tahoma"/><a:font script="Ethi" typeface="Nyala"/><a:font script="Beng" typeface="Vrinda"/><a:font script="Gujr" typeface="Shruti"/><a:font script="Khmr" typeface="MoolBoran"/><a:font script="Knda" typeface="Tunga"/><a:font script="Guru" typeface="Raavi"/><a:font script="Cans" typeface="Euphemia"/><a:font script="Cher" typeface="Plantagenet Cherokee"/><a:font script="Yiii" typeface="Microsoft Yi Baiti"/><a:font script="Tibt" typeface="Microsoft Himalaya"/><a:font script="Thaa" typeface="MV Boli"/><a:font script="Deva" typeface="Mangal"/><a:font script="Telu" typeface="Gautami"/><a:font script="Taml" typeface="Latha"/><a:font script="Syrc" typeface="Estrangelo Edessa"/><a:font script="Orya" typeface="Kalinga"/><a:font script="Mlym" typeface="Kartika"/><a:font script="Laoo" typeface="DokChampa"/><a:font script="Sinh" typeface="Iskoola Pota"/><a:font script="Mong" typeface="Mongolian Baiti"/><a:font script="Viet" typeface="Times New Roman"/><a:font script="Uigh" typeface="Microsoft Uighur"/><a:font script="Geor" typeface="Sylfaen"/></a:majorFont><a:minorFont><a:latin typeface="Calibri"/><a:ea typeface=""/><a:cs typeface=""/><a:font script="Jpan" typeface="ＭＳ Ｐゴシック"/><a:font script="Hang" typeface="맑은 고딕"/><a:font script="Hans" typeface="宋体"/><a:font script="Hant" typeface="新細明體"/><a:font script="Arab" typeface="Arial"/><a:font script="Hebr" typeface="Arial"/><a:font script="Thai" typeface="Tahoma"/><a:font script="Ethi" typeface="Nyala"/><a:font script="Beng" typeface="Vrinda"/><a:font script="Gujr" typeface="Shruti"/><a:font script="Khmr" typeface="DaunPenh"/><a:font script="Knda" typeface="Tunga"/><a:font script="Guru" typeface="Raavi"/><a:font script="Cans" typeface="Euphemia"/><a:font script="Cher" typeface="Plantagenet Cherokee"/><a:font script="Yiii" typeface="Microsoft Yi Baiti"/><a:font script="Tibt" typeface="Microsoft Himalaya"/><a:font script="Thaa" typeface="MV Boli"/><a:font script="Deva" typeface="Mangal"/><a:font script="Telu" typeface="Gautami"/><a:font script="Taml" typeface="Latha"/><a:font script="Syrc" typeface="Estrangelo Edessa"/><a:font script="Orya" typeface="Kalinga"/><a:font script="Mlym" typeface="Kartika"/><a:font script="Laoo" typeface="DokChampa"/><a:font script="Sinh" typeface="Iskoola Pota"/><a:font script="Mong" typeface="Mongolian Baiti"/><a:font script="Viet" typeface="Arial"/><a:font script="Uigh" typeface="Microsoft Uighur"/><a:font script="Geor" typeface="Sylfaen"/></a:minorFont></a:fontScheme><a:fmtScheme name="Office"><a:fillStyleLst><a:solidFill><a:schemeClr val="phClr"/></a:solidFill><a:gradFill rotWithShape="1"><a:gsLst><a:gs pos="0"><a:schemeClr val="phClr"><a:tint val="50000"/><a:satMod val="300000"/></a:schemeClr></a:gs><a:gs pos="35000"><a:schemeClr val="phClr"><a:tint val="37000"/><a:satMod val="300000"/></a:schemeClr></a:gs><a:gs pos="100000"><a:schemeClr val="phClr"><a:tint val="15000"/><a:satMod val="350000"/></a:schemeClr></a:gs></a:gsLst><a:lin ang="16200000" scaled="1"/></a:gradFill><a:gradFill rotWithShape="1"><a:gsLst><a:gs pos="0"><a:schemeClr val="phClr"><a:shade val="51000"/><a:satMod val="130000"/></a:schemeClr></a:gs><a:gs pos="80000"><a:schemeClr val="phClr"><a:shade val="93000"/><a:satMod val="130000"/></a:schemeClr></a:gs><a:gs pos="100000"><a:schemeClr val="phClr"><a:shade val="94000"/><a:satMod val="135000"/></a:schemeClr></a:gs></a:gsLst><a:lin ang="16200000" scaled="0"/></a:gradFill></a:fillStyleLst><a:lnStyleLst><a:ln w="9525" cap="flat" cmpd="sng" algn="ctr"><a:solidFill><a:schemeClr val="phClr"><a:shade val="95000"/><a:satMod val="105000"/></a:schemeClr></a:solidFill><a:prstDash val="solid"/></a:ln><a:ln w="25400" cap="flat" cmpd="sng" algn="ctr"><a:solidFill><a:schemeClr val="phClr"/></a:solidFill><a:prstDash val="solid"/></a:ln><a:ln w="38100" cap="flat" cmpd="sng" algn="ctr"><a:solidFill><a:schemeClr val="phClr"/></a:solidFill><a:prstDash val="solid"/></a:ln></a:lnStyleLst><a:effectStyleLst><a:effectStyle><a:effectLst><a:outerShdw blurRad="40000" dist="20000" dir="5400000" rotWithShape="0"><a:srgbClr val="000000"><a:alpha val="38000"/></a:srgbClr></a:outerShdw></a:effectLst></a:effectStyle><a:effectStyle><a:effectLst><a:outerShdw blurRad="40000" dist="23000" dir="5400000" rotWithShape="0"><a:srgbClr val="000000"><a:alpha val="35000"/></a:srgbClr></a:outerShdw></a:effectLst></a:effectStyle><a:effectStyle><a:effectLst><a:outerShdw blurRad="40000" dist="23000" dir="5400000" rotWithShape="0"><a:srgbClr val="000000"><a:alpha val="35000"/></a:srgbClr></a:outerShdw></a:effectLst><a:scene3d><a:camera prst="orthographicFront"><a:rot lat="0" lon="0" rev="0"/></a:camera><a:lightRig rig="threePt" dir="t"><a:rot lat="0" lon="0" rev="1200000"/></a:lightRig></a:scene3d><a:sp3d><a:bevelT w="63500" h="25400"/></a:sp3d></a:effectStyle></a:effectStyleLst><a:bgFillStyleLst><a:solidFill><a:schemeClr val="phClr"/></a:solidFill><a:gradFill rotWithShape="1"><a:gsLst><a:gs pos="0"><a:schemeClr val="phClr"><a:tint val="40000"/><a:satMod val="350000"/></a:schemeClr></a:gs><a:gs pos="40000"><a:schemeClr val="phClr"><a:tint val="45000"/><a:shade val="99000"/><a:satMod val="350000"/></a:schemeClr></a:gs><a:gs pos="100000"><a:schemeClr val="phClr"><a:shade val="20000"/><a:satMod val="255000"/></a:schemeClr></a:gs></a:gsLst><a:path path="circle"><a:fillToRect l="50000" t="-80000" r="50000" b="180000"/></a:path></a:gradFill><a:gradFill rotWithShape="1"><a:gsLst><a:gs pos="0"><a:schemeClr val="phClr"><a:tint val="80000"/><a:satMod val="300000"/></a:schemeClr></a:gs><a:gs pos="100000"><a:schemeClr val="phClr"><a:shade val="30000"/><a:satMod val="200000"/></a:schemeClr></a:gs></a:gsLst><a:path path="circle"><a:fillToRect l="50000" t="50000" r="50000" b="50000"/></a:path></a:gradFill></a:bgFillStyleLst></a:fmtScheme></a:themeElements><a:objectDefaults/><a:extraClrSchemeLst/></a:theme>
EOD;

        $this->createFolder($this->xlFolder, 'themes');
        $this->createFileWithContents($this->xlFolder, 'themes/theme1.xml', $appXmlFileContents);

        return $this;
    }

    /**
     * Creates the "core.xml" file under the "docProps" folder
     *
     * @throws \WilsonGlasser\Spout\Common\Exception\IOException If unable to create the file
     * @return FileSystemHelper
     */
    private function createCoreXmlFile()
    {
        $createdDate = (new \DateTime())->format(\DateTime::W3C);
        $coreXmlFileContents = <<<EOD
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <dcterms:created xsi:type="dcterms:W3CDTF">$createdDate</dcterms:created>
    <dcterms:modified xsi:type="dcterms:W3CDTF">$createdDate</dcterms:modified>
    <cp:revision>0</cp:revision>
</cp:coreProperties>
EOD;

        $this->createFileWithContents($this->docPropsFolder, self::CORE_XML_FILE_NAME, $coreXmlFileContents);

        return $this;
    }

    /**
     * Creates the "xl" folder under the root folder as well as its subfolders
     *
     * @throws \WilsonGlasser\Spout\Common\Exception\IOException If unable to create at least one of the folders
     * @return FileSystemHelper
     */
    private function createXlFolderAndSubFolders()
    {
        $this->xlFolder = $this->createFolder($this->rootFolder, self::XL_FOLDER_NAME);
        $this->createXlRelsFolder();
        $this->createXlWorksheetsFolder();

        //$this->createThemeXmlFile();

        return $this;
    }

    /**
     * Creates the "_rels" folder under the "xl" folder
     *
     * @throws \WilsonGlasser\Spout\Common\Exception\IOException If unable to create the folder
     * @return FileSystemHelper
     */
    private function createXlRelsFolder()
    {
        $this->xlRelsFolder = $this->createFolder($this->xlFolder, self::RELS_FOLDER_NAME);

        return $this;
    }

    /**
     * Creates the "worksheets" folder under the "xl" folder
     *
     * @throws \WilsonGlasser\Spout\Common\Exception\IOException If unable to create the folder
     * @return FileSystemHelper
     */
    private function createXlWorksheetsFolder()
    {
        $this->xlWorksheetsFolder = $this->createFolder($this->xlFolder, self::WORKSHEETS_FOLDER_NAME);
        $this->xlWorksheetsRelsFolder = $this->createFolder($this->xlWorksheetsFolder, self::RELS_FOLDER_NAME);

        return $this;
    }

    /**
     * Creates the "[Content_Types].xml" file under the root folder
     *
     * @param Worksheet[] $worksheets
     * @return FileSystemHelper
     */
    public function createContentTypesFile($worksheets)
    {
        $contentTypesXmlFileContents = <<<'EOD'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default ContentType="application/xml" Extension="xml"/>
    <Default Extension="vml" ContentType="application/vnd.openxmlformats-officedocument.vmlDrawing"/>
    <Default ContentType="application/vnd.openxmlformats-package.relationships+xml" Extension="rels"/>
    <Override ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml" PartName="/xl/workbook.xml"/>
EOD;

        /** @var Worksheet $worksheet */
        foreach ($worksheets as $worksheet) {
            $contentTypesXmlFileContents .= '<Override ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml" PartName="/xl/worksheets/sheet' . $worksheet->getId() . '.xml"/>'.PHP_EOL;
            if (count($worksheet->getExternalSheet()->getComments())) {
                $contentTypesXmlFileContents .= '<Override PartName="/xl/comments' . $worksheet->getId() . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.comments+xml"/>'.PHP_EOL;
            }
        }

        $contentTypesXmlFileContents .= <<<'EOD'
    <Override ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml" PartName="/xl/styles.xml"/>
    <Override ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml" PartName="/xl/sharedStrings.xml"/>
    <Override ContentType="application/vnd.openxmlformats-package.core-properties+xml" PartName="/docProps/core.xml"/>
    <Override ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml" PartName="/docProps/app.xml"/>
</Types>
EOD;

        $this->createFileWithContents($this->rootFolder, self::CONTENT_TYPES_XML_FILE_NAME, $contentTypesXmlFileContents);

        return $this;
    }

    /**
     * Creates the "workbook.xml" file under the "xl" folder
     *
     * @param Worksheet[] $worksheets
     * @return FileSystemHelper
     */
    public function createWorkbookFile($worksheets)
    {
        $workbookXmlFileContents = <<<'EOD'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheets>
EOD;

        /** @var Worksheet $worksheet */
        foreach ($worksheets as $worksheet) {
            $worksheetName = $worksheet->getExternalSheet()->getName();
            $worksheetVisibility = $worksheet->getExternalSheet()->isVisible() ? 'visible' : 'hidden';
            $worksheetId = $worksheet->getId();
            $workbookXmlFileContents .= '<sheet name="' . $this->escaper->escape($worksheetName) . '" sheetId="' . $worksheetId . '" r:id="rIdSheet' . $worksheetId . '" state="' . $worksheetVisibility . '"/>';
        }

        $workbookXmlFileContents .= <<<'EOD'
    </sheets>
    <definedNames/>
    <calcPr calcId="999999" calcMode="auto" calcCompleted="1" fullCalcOnLoad="0" forceFullCalc="0"/>    
</workbook>
EOD;

        $this->createFileWithContents($this->xlFolder, self::WORKBOOK_XML_FILE_NAME, $workbookXmlFileContents);

        return $this;
    }

    /**
     * Creates the "workbook.xml.res" file under the "xl/_res" folder
     *
     * @param CommentManager $commentManager
     * @param Worksheet[] $worksheets
     * @return FileSystemHelper
     */
    public function createWorkbookRelsFile($commentManager, $worksheets)
    {
        // <Relationship Id="rIdTheme" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/theme" Target="theme/theme1.xml"/>
        $workbookRelsXmlFileContents = <<<'EOD'
<?xml version="1.0" encoding="UTF-8"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rIdStyles" Target="styles.xml" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles"/>
    <Relationship Id="rIdSharedStrings" Target="sharedStrings.xml" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings"/>
EOD;

        /** @var Worksheet $worksheet */
        foreach ($worksheets as $worksheet) {
            $worksheetId = $worksheet->getId();
            $workbookRelsXmlFileContents .= '<Relationship Id="rIdSheet' . $worksheetId . '" Target="worksheets/sheet' . $worksheetId . '.xml" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet"/>';

            if (count($worksheet->getExternalSheet()->getComments())) {
                // creates a xl/worksheets/_rels file
                $sheetRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId_comments_vml'.$worksheetId.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/vmlDrawing" Target="../drawings/vmlDrawing'.$worksheetId.'.vml"/>
<Relationship Id="rId_comments'.$worksheetId.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/comments" Target="../comments'.$worksheetId.'.xml"/>
</Relationships>';

                $this->createFileWithContents($this->xlWorksheetsRelsFolder, 'sheet'.$worksheetId.'.xml.rels', $sheetRels);
            }
        }

        $workbookRelsXmlFileContents .= '</Relationships>';

        $this->createFileWithContents($this->xlRelsFolder, self::WORKBOOK_RELS_XML_FILE_NAME, $workbookRelsXmlFileContents);



        /**
         * TODO - Escrever o rels do proprio sheet1 se houver comentarios!
         */

        return $this;
    }

    /**
     * Creates the "comments{i}.xml" file under the "xl" folder
     *
     * @param CommentManager $commentManager
     * @param Worksheet[] $worksheets
     * @throws
     * @return FileSystemHelper
     */
    public function createCommentsFile($commentManager, $worksheets)
    {
        $firstComment = true;
        foreach ($worksheets as $worksheet) {
            $worksheetId = $worksheet->getId();

            if (count($worksheet->getExternalSheet()->getComments())) {
                $this->createFileWithContents($this->xlFolder, 'comments'.$worksheetId.'.xml', $commentManager->getCommentsXMLFileContent($worksheet));
                if ($firstComment) {
                    $firstComment = false;

                    $this->createFolder($this->xlFolder, 'drawings');
                }
                $this->createFileWithContents($this->xlFolder, 'drawings/vmlDrawing'.$worksheetId.'.vml', $commentManager->getVMLCommentsFileContent($worksheet));
            }
        }

        return $this;
    }

    /**
     * Creates the "styles.xml" file under the "xl" folder
     *
     * @param StyleManager $styleManager
     * @return FileSystemHelper
     */
    public function createStylesFile($styleManager)
    {
        $stylesXmlFileContents = $styleManager->getStylesXMLFileContent();
        $this->createFileWithContents($this->xlFolder, self::STYLES_XML_FILE_NAME, $stylesXmlFileContents);

        return $this;
    }

    /**
     * Zips the root folder and streams the contents of the zip into the given stream
     *
     * @param resource $streamPointer Pointer to the stream to copy the zip
     * @return void
     */
    public function zipRootFolderAndCopyToStream($streamPointer)
    {
        $zip = $this->zipHelper->createZip($this->rootFolder);

        $zipFilePath = $this->zipHelper->getZipFilePath($zip);

        // In order to have the file's mime type detected properly, files need to be added
        // to the zip file in a particular order.
        // "[Content_Types].xml" then at least 2 files located in "xl" folder should be zipped first.
        $this->zipHelper->addFileToArchive($zip, $this->rootFolder, self::CONTENT_TYPES_XML_FILE_NAME);
        $this->zipHelper->addFileToArchive($zip, $this->rootFolder, self::XL_FOLDER_NAME . '/' . self::WORKBOOK_XML_FILE_NAME);
        $this->zipHelper->addFileToArchive($zip, $this->rootFolder, self::XL_FOLDER_NAME . '/' . self::STYLES_XML_FILE_NAME);

        $this->zipHelper->addFolderToArchive($zip, $this->rootFolder, ZipHelper::EXISTING_FILES_SKIP);
        $this->zipHelper->closeArchiveAndCopyToStream($zip, $streamPointer);

        // once the zip is copied, remove it
        $this->deleteFile($zipFilePath);
    }
}

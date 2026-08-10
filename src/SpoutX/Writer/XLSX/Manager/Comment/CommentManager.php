<?php

declare(strict_types=1);

namespace SpoutX\Writer\XLSX\Manager\Comment;

use SpoutX\Common\Entity\Style\Style;
use SpoutX\Common\Helper\Escaper;
use SpoutX\Writer\Common\Entity\Comment;
use SpoutX\Writer\Common\Entity\Worksheet;
use SpoutX\Writer\Common\Helper\CellHelper;

/**
 * Class CommentManager
 * Manages comments to be applied to a cell
 */
class CommentManager
{
    /** @var Escaper\XLSX Strings escaper */
    protected Escaper\XLSX $stringsEscaper;

    private array $tmpAuthors = [];

    /**
     * @param Escaper\XLSX $stringsEscaper Strings escaper
     */
    public function __construct(Escaper\XLSX $stringsEscaper)
    {
        $this->stringsEscaper = $stringsEscaper;
    }

    /**
     * Returns the content of the "comments{x}.xml" file, given a list of comments.
     */
    public function getCommentsXMLFileContent(Worksheet $worksheet): string
    {
        $content = <<<'EOD'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<comments xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
EOD;

        $this->tmpAuthors = [];

        $content .= $this->getAuthorsSectionContent($worksheet);
        $content .= $this->getCommentsListSectionContent($worksheet);

        $content .= <<<'EOD'
</comments>
EOD;

        return $content;
    }


    public function getAuthorsSectionContent(Worksheet $worksheet): string
    {
        $content = '';

        $authors = [];

        $registeredComments = $worksheet->getExternalSheet()->getComments();
        foreach ($registeredComments as $comment) {
            if ($comment->getAuthor() !== null) {
                $authors[$comment->getAuthor()] = $comment->getAuthor();
            } else {
                $authors['Author'] = 'Author';
            }
        }

        if (count($authors)) {
            $content .= '<authors>' . PHP_EOL;
            foreach ($authors as $author) {
                $content .= '<author>' . $this->stringsEscaper->escape($author) . '</author>' . PHP_EOL;
            }
            $content .= '</authors>';
        }

        $this->tmpAuthors = array_values($authors);

        return $content;
    }

    public function getCommentsListSectionContent(Worksheet $worksheet): string
    {
        $registeredComments = $worksheet->getExternalSheet()->getComments();

        $content = '<commentList>' . PHP_EOL;
        foreach ($registeredComments as $comment) {
            if ($comment->getAuthor() === null) {
                $comment->setAuthor('Author');
            }
            $authorId = array_search($comment->getAuthor(), $this->tmpAuthors);
            $comment->setAuthorId($authorId);

            if (!empty($comment->getText()) && $comment->getText() !== null) {
                $content .= '<comment ref="' . $comment->getCell() . '"' . ($comment->getAuthorId() !== null ? ' authorId="' . $comment->getAuthorId() . '"' : '') . '>';


                // apply styles!
                $fontSize = $comment->getStyle()->getFontSize() > 0 ? $comment->getStyle()->getFontSize() : Style::DEFAULT_FONT_SIZE;
                $fontFamily = !empty($comment->getStyle()->getFontName()) && $comment->getStyle()->getFontName() !== null ? $comment->getStyle()->getFontName() : Style::DEFAULT_FONT_NAME;

                $content .= '<text>';
                if ($comment->getAuthorId() !== null && $comment->getAuthor() !== 'Author') {
                    $content .= '<r><rPr><b /><sz val="' . $fontSize . '" /><rFont val="' . $fontFamily . '" /><charset val="0" /></rPr><t xml:space="preserve">' . $comment->getAuthor() . ':</t></r>';
                }

                $content .= PHP_EOL . '<r>';

                $content .= '<rPr><sz val="' . $fontSize . '"/><rFont val="' . $fontFamily . '"/><charset val="0"/></rPr>';

                $content .= '<t xml:space="preserve">' . $this->stringsEscaper->escape($comment->getText()) . '</t></r></text>';


                $content .= '</comment>' . PHP_EOL;
            }
        }

        $content .= '</commentList>';

        return $content;
    }

    /**
     * get VML comments to XML format.
     *
     * @return string XML Output
     * @throws \Exception
     *
     */
    public function getVMLCommentsFileContent(Worksheet $pWorksheet): string
    {
        // Create XML writer
        $content = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<xml xmlns:v="urn:schemas-microsoft-com:vml"
     xmlns:o="urn:schemas-microsoft-com:office:office"
     xmlns:x="urn:schemas-microsoft-com:office:excel">
         <o:shapelayout v:ext="edit">
        <o:idmap v:ext="edit" data="1"/>
    </o:shapelayout>
    <v:shapetype id="_x0000_t202" coordsize="21600,21600" o:spt="202"
                 path="m,l,21600r21600,l21600,xe">
        <v:stroke joinstyle="miter"/>
        <v:path gradientshapeok="t" o:connecttype="rect"/>
    </v:shapetype>';


        // Comments cache
        $comments = $pWorksheet->getExternalSheet()->getComments();

        // Loop through comments
        foreach ($comments as $value) {
            $content .= $this->writeVMLComment($value);
        }

        $content .= '</xml>';

        // Return
        return $content;
    }

    /**
     * Write VML comment to XML format.
     *
     * @param Comment $pComment Comment
     */
    private function writeVMLComment(Comment $pComment): string
    {
        // Metadata
        [$column, $row] = CellHelper::coordinateFromString($pComment->getCell());
        $column = CellHelper::getColumnToIndexFromCellIndex($column) + 1;
        $id = 1024 + $column + $row;
        $id = substr((string) $id, 0, 4);

        $bgColor = (!empty($pComment->getStyle()->getBackgroundColor()) ? $pComment->getStyle()->getBackgroundColor() : Comment::DEFAULT_BACKGROUND_COLOR);

        $content = '<v:shape id="_x0000_s' . $id . '" type="#_x0000_t202" style="position:absolute; margin-left:' . $pComment->getMarginLeft() . ';margin-top:' . $pComment->getMarginTop() . ';width:' . $pComment->getWidth() . ';height:' . $pComment->getHeight() . ';z-index:1; visibility:' . ($pComment->getVisible() ? 'visible' : 'hidden') . '" fillcolor="' . $bgColor . '" 
             o:insetmode="auto">
        <v:fill color2="' . $bgColor . '"/>
        <v:shadow on="t" color="black" obscured="t"/>
        <v:path o:connecttype="none"/>
        <v:textbox style="mso-direction-alt:auto">
            <div style="text-align:left"></div>
        </v:textbox>
        <x:ClientData ObjectType="Note">
            <x:MoveWithCells/>
            <x:SizeWithCells/>
            <x:AutoFill>False</x:AutoFill>
            <x:Row>' . ($row - 1) . '</x:Row>
            <x:Column>' . ($column - 1) . '</x:Column>
        </x:ClientData>
    </v:shape>' . PHP_EOL;

        return $content;
    }

}

<?php

declare(strict_types=1);

namespace SpoutX\Common\Entity;

/**
 * A rich-text cell value: an ordered list of individually-formatted
 * {@see TextRun} pieces. Assign one as a Cell value to write a cell whose text
 * mixes fonts/colors/bold/italic, e.g. new Cell(new RichText(...)).
 */
class RichText
{
    /** @var TextRun[] */
    private array $runs;

    public function __construct(TextRun ...$runs)
    {
        $this->runs = $runs;
    }

    public function addRun(TextRun $run): self
    {
        $this->runs[] = $run;

        return $this;
    }

    /**
     * @return TextRun[]
     */
    public function getRuns(): array
    {
        return $this->runs;
    }

    /**
     * The concatenated plain text of all runs.
     */
    public function getPlainText(): string
    {
        return implode('', array_map(static fn (TextRun $run): string => $run->text, $this->runs));
    }
}

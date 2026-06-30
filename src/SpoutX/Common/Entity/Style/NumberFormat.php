<?php

declare(strict_types=1);

namespace SpoutX\Common\Entity\Style;

/**
 * Class NumberFormat
 * This class provides constants and functions to work with Number Formats
 */
class NumberFormat
{
    protected string $formatCode = '';
    protected ?int $id = null;

    public function __construct(string $formatCode = '')
    {
        $this->setFormatCode($formatCode);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getFormatCode(): string
    {
        return $this->formatCode;
    }

    public function setFormatCode(string $formatCode): self
    {
        $this->formatCode = str_replace('"', '&quot;', $formatCode);

        return $this;
    }
}

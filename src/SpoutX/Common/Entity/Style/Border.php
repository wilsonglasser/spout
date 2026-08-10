<?php

declare(strict_types=1);

namespace SpoutX\Common\Entity\Style;

class Border
{
    /**
     * The border parts, keyed by edge name.
     *
     * @var array<string, BorderPart>
     */
    private array $parts = [];

    /**
     * @param BorderPart[] $borderParts
     */
    public function __construct(array $borderParts = [])
    {
        $this->setParts($borderParts);
    }

    public function getPart(BorderName $name): ?BorderPart
    {
        return $this->parts[$name->value] ?? null;
    }

    public function hasPart(BorderName $name): bool
    {
        return isset($this->parts[$name->value]);
    }

    /**
     * @return array<string, BorderPart>
     */
    public function getParts(): array
    {
        return $this->parts;
    }

    /**
     * @param BorderPart[] $parts
     */
    public function setParts(array $parts): void
    {
        $this->parts = [];
        foreach ($parts as $part) {
            $this->addPart($part);
        }
    }

    public function addPart(BorderPart $borderPart): self
    {
        $this->parts[$borderPart->getName()->value] = $borderPart;

        return $this;
    }
}

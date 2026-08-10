<?php

declare(strict_types=1);

namespace SpoutX\Writer\Common\Manager\Style;

use SpoutX\Common\Entity\Style\Style;

/**
 * Class StyleRegistry
 * Registry for all used styles
 */
class StyleRegistry
{
    /** @var array [SERIALIZED_STYLE] => [STYLE_ID] mapping table, keeping track of the registered styles */
    protected array $serializedStyleToStyleIdMappingTable = [];

    /** @var array [STYLE_ID] => [STYLE] mapping table, keeping track of the registered styles */
    protected array $styleIdToStyleMappingTable = [];

    public function __construct(Style $defaultStyle)
    {
        // This ensures that the default style is the first one to be registered
        $this->registerStyle($defaultStyle);
    }

    /**
     * Registers the given style as a used style.
     * Duplicate styles won't be registered more than once.
     *
     * @param Style $style The style to be registered
     * @return Style The registered style, updated with an internal ID.
     */
    public function registerStyle(Style $style): Style
    {
        $serializedStyle = $this->serialize($style);

        if (!$this->hasStyleAlreadyBeenRegistered($style)) {
            $nextStyleId = count($this->serializedStyleToStyleIdMappingTable);
            $style->setId($nextStyleId);

            $this->serializedStyleToStyleIdMappingTable[$serializedStyle] = $nextStyleId;
            $this->styleIdToStyleMappingTable[$nextStyleId] = $style;
        }

        return $this->getStyleFromSerializedStyle($serializedStyle);
    }

    /**
     * Returns whether the given style has already been registered.
     *
     */
    protected function hasStyleAlreadyBeenRegistered(Style $style): bool
    {
        $serializedStyle = $this->serialize($style);

        // Using isset here because it is way faster than array_key_exists...
        return isset($this->serializedStyleToStyleIdMappingTable[$serializedStyle]);
    }

    /**
     * Returns the registered style associated to the given serialization.
     *
     * @param string $serializedStyle The serialized style from which the actual style should be fetched from
     */
    protected function getStyleFromSerializedStyle(string $serializedStyle): Style
    {
        $styleId = $this->serializedStyleToStyleIdMappingTable[$serializedStyle];

        return $this->styleIdToStyleMappingTable[$styleId];
    }

    /**
     * @return Style[] List of registered styles
     */
    public function getRegisteredStyles(): array
    {
        return array_values($this->styleIdToStyleMappingTable);
    }

    public function getStyleFromStyleId(int $styleId): Style
    {
        return $this->styleIdToStyleMappingTable[$styleId];
    }

    /**
     * Serializes the style for future comparison with other styles.
     * The ID is excluded from the comparison, as we only care about
     * actual style properties.
     *
     * @return string The serialized style
     */
    public function serialize(Style $style): string
    {
        // In order to be able to properly compare style, set static ID value
        $currentId = $style->getId();
        $style->setId(0);

        $serializedStyle = serialize($style);

        $style->setId($currentId);

        return $serializedStyle;
    }
}

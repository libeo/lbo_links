<?php

namespace Libeo\LboLinks\Utility;

class TagParsingUtility
{
    public static function anchorIsClosed(string $anchor): bool
    {
        return str_contains($anchor, '</a>');
    }

    public static function stripDuplicatedAttributes(string $tag, array $attributes): string
    {
        $strippedTag = $tag;
        foreach ($attributes as $attribute) {
            $attrStart = strpos($strippedTag, $attribute);
            $attrEnd = strpos($strippedTag, '" ', $attrStart) + 2;

            $strippedTag = substr($strippedTag, 0, $attrStart) . substr($strippedTag, $attrEnd);
        }
        return $strippedTag;
    }
}

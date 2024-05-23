<?php
namespace FinvoiceParser\Enums;


enum FiletypeEnum: string
{
    case XML = 'xml';
    case JSON = 'json';

    public static function getFromFullFilePath(string $fullFilePath): FiletypeEnum
    {
        $extension = pathinfo($fullFilePath, PATHINFO_EXTENSION);

        return match ($extension) {
            'xml' => self::XML,
            'json' => self::JSON,
            default => throw new \InvalidArgumentException("Unsupported file type: $extension"),
        };
    }
}
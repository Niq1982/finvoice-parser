<?php
namespace FinvoiceParser\Data;

use FinvoiceParser\Enums\FiletypeEnum;
use FinvoiceParser\DataObject\DataObject;
use FinvoiceParser\Exceptions\FileDataException;

/**
 * A class to represent the files that are being processed.
 * We save only the file path and name so we won't have to keep a lot of data in memory at once.
 *
 * Use the `getData` method to get the actual data from the file, for now only XML files are supported.
 *
 * @package FinvoiceParser\Data
 */
class FileData extends DataObject
{
    public readonly string $filePath;

    public function __construct(
        string $filePath,
        public readonly string $fileName,
        public readonly FiletypeEnum $fileType,
    ) {
        // Strip trailing slashes from the file path
        $this->filePath = rtrim($filePath, '/');
    }

    public function getFullFilePath(): string
    {
        return $this->filePath . '/' . $this->fileName;
    }

    public function exists()
    {
        return file_exists($this->getFullFilePath());
    }

    public function getData(): object
    {
        if (!$this->exists()) {
            throw new FileDataException("File does not exist: {$this->getFullFilePath()}");
        }

        return match ($this->fileType) {
            FiletypeEnum::XML => $this->loadXMLFileData(),
            default => throw new \InvalidArgumentException("Unsupported file type: {$this->fileType->value}"),
        };
    }

    public function loadXMLFileData(): \SimpleXMLElement
    {
        // Load and suppress warnings/errors, we will handle them ourselves
        $xmlElement = simplexml_load_file(filename: $this->getFullFilePath(), options: LIBXML_NOCDATA | LIBXML_NOERROR | LIBXML_NOWARNING);

        if ($xmlElement === false) {
            throw new FileDataException("Invalid XML file");
        }

        return $xmlElement;
    }
}
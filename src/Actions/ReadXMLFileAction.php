<?php
namespace FinvoiceParser\Actions;

use FinvoiceParser\Data\FinvoiceData;
use FinvoiceParser\Exceptions\ReadXMLFileActionException;

class ReadXMLFileAction
{
    private \SimpleXMLElement $xmlElement;

    public function __construct(
        private string $filePath,
    ) {
        // Load and suppress warnings/errors, we will handle them ourselves
        $xmlElement = simplexml_load_file(filename: $filePath, options: LIBXML_NOCDATA | LIBXML_NOERROR | LIBXML_NOWARNING);

        if ($xmlElement === false) {
            throw new ReadXMLFileActionException("Failed to load the XML file");
        }

        $this->xmlElement = $xmlElement;
    }

    public function execute(): FinvoiceData
    {
        try {
            return FinvoiceData::fromXMLElement($this->xmlElement);
        } catch (\Throwable $e) {
            throw new ReadXMLFileActionException("Failed to read the XML file: {$e->getMessage()}");
        }


    }
}
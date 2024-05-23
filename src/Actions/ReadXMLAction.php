<?php
namespace FinvoiceParser\Actions;

use FinvoiceParser\Data\FinvoiceXMLData;
use FinvoiceParser\Data\FinVoiceXMLData\DateData;
use FinvoiceParser\Data\FinVoiceXMLData\AmountData;
use FinvoiceParser\Exceptions\ReadXMLActionException;

class ReadXMLAction
{
    private \XMLReader $xmlReader;

    public function __construct(
        private string $file_path,
    ) {
        $this->xmlReader = self::prepareXMLReader($file_path);
    }

    private static function prepareXMLReader(string $filePath): \XMLReader
    {
        // Set error logging, so we can catch the errors
        $prev = libxml_use_internal_errors(true);

        $xmlReader = new \XMLReader();
        $success = $xmlReader->open($filePath, null, LIBXML_NOCDATA);
        $errors = libxml_get_errors();

        if (!$success || !empty($errors)) {
            $error_message = !empty($errors) ? implode('\n', $errors) : "Failed to open the XML file";
            throw new ReadXMLActionException("{$error_message}");
        }

        // Reset the logs
        libxml_use_internal_errors($prev);

        return $xmlReader;
    }

    public function execute(): FinvoiceXMLData
    {
        $data = [];

        try {
            while ($this->xmlReader->read()) {
                if ($this->xmlReader->nodeType !== \XMLReader::ELEMENT)
                    continue;

                $elementName = $this->xmlReader->name;

                $data[$elementName] = match ($elementName) {
                    'SellerPartyIdentifier' => $this->xmlReader->readString(),
                    'SellerOrganisationName' => $this->xmlReader->readString(),
                    'InvoiceNumber' => (int) $this->xmlReader->readString(),
                    'EpiAccountID' => $this->xmlReader->readString(),
                    'EpiRemittanceInfoIdentifier' => (int) $this->xmlReader->readString(),
                    'EpiInstructedAmount' => new AmountData(
                        amount: $this->xmlReader->readString(),
                        currency: $this->xmlReader->getAttribute('AmountCurrencyIdentifier') ?? 'EUR',
                    ),
                    'EpiDateOptionDate' => new DateData(
                        date: $this->xmlReader->readString(),
                        format: $this->xmlReader->getAttribute('Format') ?? 'CCYYMMDD',
                    ),
                    default => null,
                };
            }
        } catch (\Throwable $e) {
            throw new ReadXMLActionException("Failed to read the XML file: {$e->getMessage()}");
        }

        // Filter null values from the array and try to create a FinvoiceXMLData object with the data we have
        try {
            return new FinvoiceXMLData(...array_filter($data));
        } catch (\Throwable $e) {
            throw new ReadXMLActionException("Failed to create FinvoiceXMLData object: {$e->getMessage()}");
        }
    }
}
<?php
namespace FinvoiceParser\Factories;

use Brick\Money\Money;
use DateTimeImmutable;
use FinvoiceParser\Data\FileData;
use FinvoiceParser\Data\InvoiceData;
use FinvoiceParser\Enums\FiletypeEnum;
use FinvoiceParser\Exceptions\InvoiceDataException;
use FinvoiceParser\Factories\Interfaces\XMLInvoiceFactoryInterface;

/**
 * The FinvoiceFactory class is responsible for creating InvoiceData objects from Finvoice XML files.
 */
class FinvoiceFactory implements XMLInvoiceFactoryInterface
{
    public static function getFileType(): FiletypeEnum
    {
        return FiletypeEnum::XML;
    }

    public static function createInvoiceFromFile(FileData $fileData): InvoiceData
    {
        if ($fileData->fileType !== FiletypeEnum::XML) {
            throw new InvoiceDataException('Invalid file type');
        }

        if (!($fileData->getData() instanceof \SimpleXMLElement)) {
            throw new InvoiceDataException('Invalid data type');
        }

        $xml = $fileData->getData();

        /**
         * @var \SimpleXMLElement[]|null $mappedValues
         */
        $mappedValues = [
            'SellerPartyIdentifier' => $xml->SellerPartyDetails->SellerPartyIdentifier ?? null,
            'SellerOrganisationName' => $xml->SellerPartyDetails->SellerOrganisationName ?? null,
            'InvoiceNumber' => $xml->InvoiceDetails->InvoiceNumber ?? null,
            'EpiAccountID' => $xml->EpiDetails->EpiPartyDetails->EpiBeneficiaryPartyDetails->EpiAccountID ?? null,
            'EpiRemittanceInfoIdentifier' => $xml->EpiDetails->EpiPaymentInstructionDetails->EpiRemittanceInfoIdentifier ?? null,
            'EpiInstructedAmount' => $xml->EpiDetails->EpiPaymentInstructionDetails->EpiInstructedAmount ?? null,
            'EpiDateOptionDate' => $xml->EpiDetails->EpiPaymentInstructionDetails->EpiDateOptionDate ?? null,
        ];

        // Check that all the necessary data is present and not empty
        foreach ($mappedValues as $key => $value) {
            if ($value === null) {
                throw new InvoiceDataException("Missing required value {$key}");
            }
            if (empty($value)) {
                throw new InvoiceDataException("Value for {$key} is empty");
            }
        }

        return new InvoiceData(
            supplierBusinessID: (string) $mappedValues['SellerPartyIdentifier'],
            supplierName: (string) $mappedValues['SellerOrganisationName'],
            invoiceNumber: (int) $mappedValues['InvoiceNumber'],
            bankAccount: (string) $mappedValues['EpiAccountID'],
            bankReferenceNumber: (string) $mappedValues['EpiRemittanceInfoIdentifier'],
            paymentSum: Money::of(
                str_replace(',', '.', (string) $mappedValues['EpiInstructedAmount']),
                $mappedValues['EpiInstructedAmount']->attributes()->AmountCurrencyIdentifier,
            ),
            paymentDueDate: DateTimeImmutable::createFromFormat(
                // TODO: This is a hardcoded format, we should make it dynamic, but PHP does not support formats like CCYYMMDD out of the box, thus making it out of scope for this task
                'Ymd',
                (string) $mappedValues['EpiDateOptionDate'],
            ),
        );

    }
}
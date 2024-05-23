<?php

namespace FinvoiceParser\Data;

use Brick\Money\Money;
use FinvoiceParser\DataObject\DataObject;
use FinvoiceParser\Exceptions\FinvoiceDataException;

class FinvoiceData extends DataObject
{
    public function __construct(
        public readonly string $supplierBusinessID,
        public readonly string $supplierName,
        public readonly int $invoiceNumber,
        public readonly string $bankAccount,
        public readonly int $bankReferenceNumber,
        public readonly Money $paymentSum,
        public readonly \DateTimeImmutable $paymentDueDate
    ) {
    }

    /**
     * Factory method to create a FinvoiceData object from a SimpleXMLElement object.
     *
     * This is an example, in real life this would be created per source XML format.
     */
    public static function fromXMLElement(\SimpleXMLElement $xml): self
    {
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
                throw new FinvoiceDataException("Missing required value {$key}");
            }
            if (empty($value)) {
                throw new FinvoiceDataException("Value for {$key} is empty");
            }
        }
        return new self(
            supplierBusinessID: (string) $mappedValues['SellerPartyIdentifier'],
            supplierName: (string) $mappedValues['SellerOrganisationName'],
            invoiceNumber: (int) $mappedValues['InvoiceNumber'],
            bankAccount: (string) $mappedValues['EpiAccountID'],
            bankReferenceNumber: (string) $mappedValues['EpiRemittanceInfoIdentifier'],
            paymentSum: Money::of(
                str_replace(',', '.', (string) $mappedValues['EpiInstructedAmount']),
                $mappedValues['EpiInstructedAmount']->attributes()->AmountCurrencyIdentifier,
            ),
            paymentDueDate: \DateTimeImmutable::createFromFormat(
                // TODO: This is a hardcoded format, we should make it dynamic, but PHP does not support formats like CCYYMMDD out of the box, thus making it out of scope for this task
                'Ymd',
                (string) $mappedValues['EpiDateOptionDate'],
            ),
        );
    }
}
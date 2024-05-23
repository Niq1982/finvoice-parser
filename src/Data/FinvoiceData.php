<?php

namespace FinvoiceParser\Data;

use Brick\Money\Money;
use FinvoiceParser\DataObject\DataObject;

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
     * Factory method to create a FinvoiceData object from FinvoiceXMLData object.
     */
    public static function fromFinvoiceXMLData(FinvoiceXMLData $data): self
    {
        return new self(
            supplierBusinessID: $data->SellerPartyIdentifier,
            supplierName: $data->SellerOrganisationName,
            invoiceNumber: $data->InvoiceNumber,
            bankAccount: $data->EpiAccountID,
            bankReferenceNumber: $data->EpiRemittanceInfoIdentifier,
            paymentSum: Money::of($data->EpiInstructedAmount->amount, $data->EpiInstructedAmount->currency),
            paymentDueDate: \DateTimeImmutable::createFromFormat($data->EpiDateOptionDate->format, $data->EpiDateOptionDate->date),
        );
    }
}
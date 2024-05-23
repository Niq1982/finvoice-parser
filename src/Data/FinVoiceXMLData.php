<?php
namespace FinvoiceParser\Data;

use FinvoiceParser\Data\FinVoiceXMLData\AmountData;
use FinvoiceParser\Data\FinVoiceXMLData\DateData;
use FinvoiceParser\DataObject\DataObject;

/**
 * A representation of the raw Finvoice XML data of a single invoice,
 * limited to the data we need for this parser for now
 */
class FinvoiceXMLData extends DataObject
{
    public function __construct(
        public readonly string $SellerPartyIdentifier, // Supplier Business ID
        public readonly string $SellerOrganisationName, // Supplier Name
        public readonly int $InvoiceNumber, // Invoice number
        public readonly string $EpiAccountID, // Bank account in IBAN format
        public readonly int $EpiRemittanceInfoIdentifier, // Bank reference number in SPY format
        public readonly AmountData $EpiInstructedAmount, // Payment sum
        public readonly DateData $EpiDateOptionDate, // Payment/due date
    ) {

    }
}
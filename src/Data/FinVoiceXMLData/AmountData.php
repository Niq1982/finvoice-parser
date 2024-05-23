<?php
namespace FinvoiceParser\Data\FinVoiceXMLData;

use FinvoiceParser\DataObject\DataObject;

class AmountData extends DataObject
{
    public function __construct(
        public string $amount,
        public string $currency,
    ) {
    }
}
<?php
namespace FinvoiceParser\Data\FinVoiceXMLData;

use FinvoiceParser\DataObject\DataObject;

class DateData extends DataObject
{
    public function __construct(
        public string $date,
        public string $format,
    ) {
    }
}
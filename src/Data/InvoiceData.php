<?php

namespace FinvoiceParser\Data;

use Brick\Money\Money;
use FinvoiceParser\DataObject\DataObject;

class InvoiceData extends DataObject
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
}

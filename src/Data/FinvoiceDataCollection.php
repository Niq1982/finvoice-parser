<?php
namespace FinvoiceParser\Data;

use FinvoiceParser\Data\FinvoiceData;
use FinvoiceParser\DataObject\DataObject;
use FinvoiceParser\DataObject\DataObjectCollection;

class FinvoiceDataCollection extends DataObjectCollection
{
    public function current(): FinvoiceData
    {
        return parent::current();
    }

    /**
     * @param FinvoiceData $item
     */
    public function add(DataObject $item): void
    {
        if (!$item instanceof FinvoiceData) {
            throw new \InvalidArgumentException('Only FinvoiceData objects can be added to this collection');
        }

        parent::add($item);
    }
    public function first(): FinvoiceData|null
    {
        return parent::first();
    }

    /**
     * Check if the collection already contains the invoice by comparing the invoice number and supplier business ID
     */
    public function contains(FinvoiceData $item): bool
    {
        $duplicates = array_filter(
            $this->items,
            fn(FinvoiceData $invoice) => $invoice->invoiceNumber === $item->invoiceNumber && $invoice->supplierBusinessID === $item->supplierBusinessID
        );

        return count($duplicates) > 0;
    }
}
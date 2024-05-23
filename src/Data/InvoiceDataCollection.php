<?php

namespace FinvoiceParser\Data;

use FinvoiceParser\Data\InvoiceData;
use FinvoiceParser\DataObject\DataObject;
use FinvoiceParser\DataObject\DataObjectCollection;

class InvoiceDataCollection extends DataObjectCollection
{
    public function current(): InvoiceData
    {
        return current($this->items);
    }

    /**
     * @param InvoiceData $item
     */
    public function add(DataObject $item): void
    {
        if (!$item instanceof InvoiceData) {
            throw new \InvalidArgumentException('Only FinvoiceData objects can be added to this collection');
        }

        parent::add($item);
    }
    public function first(): InvoiceData|null
    {
        return parent::first();
    }

    /**
     * Check if the collection already contains the invoice by comparing the invoice number and supplier business ID
     */
    public function contains(InvoiceData $item): bool
    {
        $duplicates = array_filter(
            $this->items,
            fn(InvoiceData $invoice) => $invoice->invoiceNumber === $item->invoiceNumber && $invoice->supplierBusinessID === $item->supplierBusinessID
        );

        return count($duplicates) > 0;
    }

    /**
     * Sort the collection in place using the provided sort function
     */
    public function sort(callable $sortFunction): void
    {
        usort($this->items, $sortFunction);
    }
}

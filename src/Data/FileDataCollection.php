<?php
namespace FinvoiceParser\Data;

use FinvoiceParser\Data\FileData;
use FinvoiceParser\DataObject\DataObject;
use FinvoiceParser\DataObject\DataObjectCollection;

class FileDataCollection extends DataObjectCollection
{
    public function current(): FileData
    {
        return current($this->items);
    }

    /**
     * @param FileData $item
     */
    public function add(DataObject $item): void
    {
        if (!$item instanceof FileData) {
            throw new \InvalidArgumentException('Only FileData objects can be added to this collection');
        }
        parent::add($item);
    }
    public function first(): FileData|null
    {
        return parent::first();
    }
}
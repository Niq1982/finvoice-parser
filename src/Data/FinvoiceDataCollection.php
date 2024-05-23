<?php
namespace FinvoiceParser\Data;

use FinvoiceParser\Data\FinVoiceData;
use FinvoiceParser\DataObject\DataObject;
use FinvoiceParser\DataObject\DataObjectCollection;

class FinVoiceDataCollection extends DataObjectCollection
{
    public function current(): FinVoiceData
    {
        return parent::current();
    }

    /**
     * @param FinVoiceData $item
     */
    public function add(DataObject $item): void
    {
        if (!$item instanceof FinVoiceData) {
            throw new \InvalidArgumentException('Only FinVoiceData objects can be added to this collection');
        }

        parent::add($item);
    }
    public function first(): FinVoiceData|null
    {
        return parent::first();
    }
}
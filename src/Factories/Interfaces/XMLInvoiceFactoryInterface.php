<?php

namespace FinvoiceParser\Factories\Interfaces;

use FinvoiceParser\Data\FileData;
use FinvoiceParser\Data\InvoiceData;
use FinvoiceParser\Exceptions\InvoiceDataException;
use FinvoiceParser\Factories\Interfaces\InvoiceFactoryInterface;

/**
 * The interface for XML invoice factories. The main purpose of this interface is to provide a common method for creating
 * InvoiceData objects from XML files.
 *
 * @package FinvoiceParser\Factories\Interfaces
 */
interface XMLInvoiceFactoryInterface extends InvoiceFactoryInterface
{
    /**
     * Create an InvoiceData object from a SimpleXMLElement
     *
     * @param \SimpleXMLElement $xml
     * @throws InvoiceDataException
     * @return InvoiceData
     */
    public static function createInvoiceFromFile(FileData $fileData): InvoiceData;
}

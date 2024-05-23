<?php

namespace FinvoiceParser\Factories\Interfaces;

use FinvoiceParser\Data\FileData;
use FinvoiceParser\Data\InvoiceData;
use FinvoiceParser\Enums\FiletypeEnum;

/**
 * The interface for invoice factories. The main purpose of this interface is to provide a common method for creating
 * InvoiceData objects from different types and formats of files.
 *
 * This interface should be implemented only in the per-type interfaces like XML or JSON factories.
 *
 * The actual classes should implement those per-type interfaces. For example, FinvoiceFactory implements XMLInvoiceFactoryInterface.
 *
 * @see XMLInvoiceFactoryInterface
 * @see FinvoiceFactory
 */
interface InvoiceFactoryInterface
{
    public static function createInvoiceFromFile(FileData $fileData): InvoiceData;

    public static function getFileType(): FiletypeEnum;
}

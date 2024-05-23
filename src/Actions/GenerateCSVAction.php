<?php

namespace FinvoiceParser\Actions;

use FinvoiceParser\Data\InvoiceData;
use FinvoiceParser\Data\InvoiceDataCollection;

/**
 * Generate a CSV file contents from the given InvoiceDataCollection
 */
class GenerateCSVAction
{
    /**
     * The column names for the CSV file.
     *
     * IMPORTANT: If you modify this, remember to update the column structure in the row generator method!
     *
     * @see self::generateCSVRowFromFinvoiceData
     * @var string[]
     */
    private static $columnNames = [
        'Supplier Business ID',
        'Supplier Name',
        'Invoice number',
        'Bank account',
        'Bank reference number',
        'Payment sum',
        // Yep, added the currency, since it's a good practice to have it whenever dealing with money
        'Payment sum currency',
        'Payment/due date'
    ];

    /**
     * The date format for outputting dates as a string
     *
     * @var string
     */
    private static string $dateFormat = 'Y-m-d';

    /**
     * The end of line character for the CSV file
     *
     * @var string
     */
    private static string $endOfLine = PHP_EOL;

    public function __construct(
        private InvoiceDataCollection $invoiceDataCollection,
        private string $separator,
        private ?string $enclosure,
    ) {
    }

    public function execute(): string
    {
        $rows = [
            // Add column names at the top
            implode($this->separator, self::$columnNames),
            // Spread the generated data rows
            ...array_map(
                fn(InvoiceData $data) => self::generateCSVRowFromFinvoiceData($data),
                $this->invoiceDataCollection->toArray()
            )
        ];

        return implode(self::$endOfLine, $rows);
    }

    /**
     * Generate a CSV row from the given Invoice data
     *
     * @param InvoiceData $data
     * @return string
     */
    private function generateCSVRowFromFinvoiceData(InvoiceData $data): string
    {
        $csv_values = [
            $data->supplierBusinessID,
            $data->supplierName,
            $data->invoiceNumber,
            $data->bankAccount,
            $data->bankReferenceNumber,
            $data->paymentSum->getAmount()->toFloat(),
            $data->paymentSum->getCurrency()->getCurrencyCode(),
            $data->paymentDueDate->format(self::$dateFormat)
        ];

        // Wrap the values in enclosures if they are set
        if ($this->enclosure) {
            $csv_values = array_map(fn($value) => $this->enclosure . $value . $this->enclosure, $csv_values);
        }

        return implode(',', $csv_values);
    }
}

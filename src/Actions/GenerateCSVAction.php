<?php
namespace FinvoiceParser\Actions;

use FinvoiceParser\Data\FinvoiceData;
use FinvoiceParser\Data\FinvoiceDataCollection;

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
        'Payment sum currency',
        'Payment/due date'
    ];

    private static $dateFormat = 'Y-m-d';

    public function __construct(
        private FinvoiceDataCollection $finvoiceDataCollection,
        private string $separator,
        private ?string $enclosure,
    ) {
    }

    public function execute(): string
    {
        $rows = [
            implode($this->separator, self::$columnNames),
            ...array_map(
                fn(FinvoiceData $data) => self::generateCSVRowFromFinvoiceData($data),
                $this->finvoiceDataCollection->toArray()
            )
        ];

        return implode(PHP_EOL, $rows);

    }

    private function generateCSVRowFromFinvoiceData(FinvoiceData $data): string
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

        if ($this->enclosure) {
            $csv_values = array_map(fn($value) => $this->enclosure . $value . $this->enclosure, $csv_values);
        }

        return implode(',', $csv_values);
    }
}
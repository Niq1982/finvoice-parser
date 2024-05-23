<?php
namespace FinvoiceParser\Actions;

use FinvoiceParser\Data\FinVoiceData;
use FinvoiceParser\Data\FinVoiceDataCollection;

class GenerateCSVFromFinVoiceDataCollection
{
    /**
     * The column names for the CSV file.
     *
     * IMPORTANT: If you modify this, remember to update the column structure in the row generator method!
     *
     * @see self::generateCSVRowFromFinVoiceData
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

    private static $dateFormat = 'YYYY-MM-DD';

    public function __construct(
        private FinVoiceDataCollection $finVoiceDataCollection,
        private string $separator,
        private ?string $enclosure,
    ) {
    }

    public function execute(): string
    {
        $rows = [
            implode($this->separator, self::$columnNames),
            ...array_map(
                fn(FinVoiceData $data) => self::generateCSVRowFromFinVoiceData($data),
                $this->finVoiceDataCollection->toArray()
            )
        ];

        return implode(PHP_EOL, $rows);

    }

    private function generateCSVRowFromFinVoiceData(FinVoiceData $data): string
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
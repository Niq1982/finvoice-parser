<?php
namespace FinvoiceParser\Commands;

use FinvoiceParser\Actions\ReadXMLAction;
use FinvoiceParser\Data\FinvoiceXMLData;

class ParseFiles
{
    private string $inputFolder = 'invoices/';
    private string $outputFileName = 'payments.csv';
    private string $csvSeparator = ';';
    private string $csvEnclosure = '';

    public function __construct(
        array $args = [] // named command line arguments
    ) {
        $sanitizedArgs = array_map(fn($arg) => escapeshellarg($arg), $args);

        $this->inputFolder = $sanitizedArgs['input'] ?? $this->inputFolder;
        $this->outputFileName = $sanitizedArgs['output'] ?? $this->outputFileName;
        $this->csvSeparator = $sanitizedArgs['separator'] ?? $this->csvSeparator;
        $this->csvEnclosure = $sanitizedArgs['enclosure'] ?? $this->csvEnclosure;
    }

    public function execute(): void
    {
        $files = scandir($this->inputFolder);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $xml = simplexml_load_file($this->inputFolder . '/' . $file);
            $json = json_encode($xml);
            $array = json_decode($json, true);

            $outputFile = $this->outputDir . '/' . pathinfo($file, PATHINFO_FILENAME) . '.json';
            file_put_contents($outputFile, json_encode($array, JSON_PRETTY_PRINT));
        }
    }

    private function parseFile(string $filename): FinvoiceXMLData
    {
        $rawData = (new ReadXMLAction($filename))->execute();


    }
}
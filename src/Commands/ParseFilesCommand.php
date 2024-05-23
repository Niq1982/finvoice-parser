<?php
namespace FinvoiceParser\Commands;

use FinvoiceParser\Actions\ReadXMLFileAction;
use FinvoiceParser\Data\FinvoiceDataCollection;
use FinvoiceParser\Actions\GenerateCSVAction;
use FinvoiceParser\Exceptions\ParseFilesCommandException;

class ParseFilesCommand
{
    const STYLE_BOLD = "\033[1m";
    const STYLE_RED = "\033[31m";
    const STYLE_GREEN = "\033[32m";
    const STYLE_YELLOW = "\033[33m";
    const STYLE_END = "\033[0m";
    private string $inputFolder = 'examples';
    private string $outputFileName = 'payments.csv';
    private string $csvSeparator = ';';
    private string $csvEnclosure = '';

    public function __construct(
        array $args = [] // named command line arguments
    ) {
        // Escape the args and remove extra quotes
        $sanitizedArgs = array_map(
            fn($arg) => str_replace("'", '', escapeshellarg(trim($arg))),
            $args
        );

        $this->inputFolder = rtrim($sanitizedArgs['input'] ?? $this->inputFolder, '/');
        $this->outputFileName = $sanitizedArgs['output'] ?? $this->outputFileName;
        $this->csvSeparator = $sanitizedArgs['separator'] ?? $this->csvSeparator;
        $this->csvEnclosure = $sanitizedArgs['enclosure'] ?? $this->csvEnclosure;
    }

    public function execute(): void
    {
        try {
            $xmlFiles = self::getAllXMLFilesFromFolder($this->inputFolder);
        } catch (ParseFilesCommandException $e) {
            echo self::STYLE_RED . "Error: " . $e->getMessage() . self::STYLE_END . PHP_EOL;
            die();
        }

        if (empty($xmlFiles)) {
            echo self::STYLE_YELLOW . "No XML files found in the folder $this->inputFolder" . self::STYLE_END . PHP_EOL;
            die();
        }

        echo PHP_EOL . self::STYLE_BOLD . "Found total of " . count($xmlFiles) . " files" . self::STYLE_END . PHP_EOL . PHP_EOL;
        echo "Processing..." . PHP_EOL . PHP_EOL;

        $generated = 0;
        $skipped = 0;
        $errors = 0;

        $finvoiceDataCollection = FinvoiceDataCollection::create();

        // Start collecting the data from the XML files
        foreach ($xmlFiles as $file) {
            /**
             * Check if file is reasonable sized (max 1 MB) to avoid crashing, since we are reading
             * the whole file into memory at once. Processing larger files is out of scope for now.
             *
             * If later on we would need to process larger files, we have to
             * refactor the XML reader to read the file in chunks or handle it otherwise.
             *
             * @see ReadXMLFileAction::prepareXMLReader
             */
            if (filesize($file) > 1024 * 1024) {
                echo self::STYLE_RED . "Error: File $file is too large" . self::STYLE_END . PHP_EOL;
                $errors++;
                continue;
            }

            try {
                $finvoiceData = (new ReadXMLFileAction($file))->execute();

                // Check if the invoice is already in the collection
                if ($finvoiceDataCollection->contains($finvoiceData)) {
                    echo self::STYLE_YELLOW . "Invoice with number {$finvoiceData->invoiceNumber} and business ID {$finvoiceData->supplierBusinessID} exists already in the collection, skipping" . self::STYLE_END . PHP_EOL;
                    $skipped++;
                    continue;
                }

                $finvoiceDataCollection->add($finvoiceData);
                $generated++;
            } catch (\Exception $e) {
                echo self::STYLE_RED . "Error while processing file $file: " . $e->getMessage() . self::STYLE_END . PHP_EOL;
                $errors++;
            }
        }

        if (!$finvoiceDataCollection->empty()) {
            // Generate the CSV content from the collected data
            try {
                $csvContent = (
                    new GenerateCSVAction(
                        $finvoiceDataCollection,
                        $this->csvSeparator,
                        $this->csvEnclosure
                    )
                )->execute();
            } catch (\Exception $e) {
                // Something went wrong while generating the CSV content. Let's stop here.
                echo self::STYLE_RED . "Error while generating the CSV content: " . $e->getMessage() . self::STYLE_END . PHP_EOL;
                die();
            }

            try {
                file_put_contents($this->outputFileName, $csvContent);
            } catch (\Exception $e) {
                echo self::STYLE_RED . "Error while writing the CSV file: " . $e->getMessage() . self::STYLE_END . PHP_EOL;
                die();
            }
        }

        echo PHP_EOL . self::STYLE_BOLD . "Done!" . self::STYLE_END . PHP_EOL;
        echo '----------------------------------------' . PHP_EOL;
        if ($generated > 0) {
            echo self::STYLE_GREEN . "Generated {$generated} payments and saved them to {$this->outputFileName}" . self::STYLE_END . PHP_EOL;
        } else {
            echo self::STYLE_YELLOW . "Found 0 payments to generate" . self::STYLE_END . PHP_EOL;
        }
        echo self::STYLE_YELLOW . "Skipped {$skipped} duplicate invoices" . self::STYLE_END . PHP_EOL;
        echo self::STYLE_RED . "Skipped {$errors} invoices because errors" . self::STYLE_END . PHP_EOL;
    }



    private static function getAllXMLFilesFromFolder(string $folder): array
    {

        $exists = file_exists($folder);
        if (!$exists) {
            throw new ParseFilesCommandException("Folder $folder doesnt exist");
        }

        $files = scandir($folder);
        if ($files === false) {
            throw new ParseFilesCommandException("Error reading $folder");
        }

        // Filter out directories
        $actualFiles = array_filter(
            $files,
            fn($file) => $file !== '.' && $file !== '..'
        );

        // Add the full path to the files
        $actualFilesWithPaths = array_map(
            fn($file) => $folder . '/' . $file,
            $actualFiles
        );

        // Filter non-XML files out
        return array_values(
            array_filter(
                $actualFilesWithPaths,
                fn($file) => pathinfo($file, PATHINFO_EXTENSION) === 'xml'
            )
        );
    }
}
<?php

namespace FinvoiceParser\Commands;

use FinvoiceParser\Data\FileData;
use FinvoiceParser\Enums\FiletypeEnum;
use FinvoiceParser\Data\FileDataCollection;
use FinvoiceParser\Actions\GenerateCSVAction;
use FinvoiceParser\Factories\FinvoiceFactory;
use FinvoiceParser\Data\InvoiceDataCollection;
use FinvoiceParser\Exceptions\ParseFilesCommandException;
use FinvoiceParser\Factories\Interfaces\InvoiceFactoryInterface;
use FinvoiceParser\Factories\Interfaces\XMLInvoiceFactoryInterface;

/**
 * Command to parse files from a specified folder and generate a CSV file from them
 *
 * For the main functionality, @see self::execute()
 */
class ParseFilesCommand
{
    /**
     * The factory to use for creating InvoiceData objects
     *
     * For example @see FinvoiceFactory
     *
     * @var InvoiceFactoryInterface
     */
    private InvoiceFactoryInterface $invoiceFactory;

    /**
     * The collection of InvoiceData objects
     *
     * @var InvoiceDataCollection
     */
    private InvoiceDataCollection $invoiceDataCollection;

    // ANSI escape codes for colors and styles
    private const STYLE_BOLD = "\033[1m";
    private const STYLE_RED = "\033[31m";
    private const STYLE_GREEN = "\033[32m";
    private const STYLE_YELLOW = "\033[33m";
    private const STYLE_END = "\033[0m";

    // The main parameters for the command
    private string $inputFolder = 'examples';
    private string $outputFileName = 'payments.csv';
    private string $csvSeparator = ';';
    private string $csvEnclosure = '';

    // Summary data
    private int $generated = 0;
    private int $skipped = 0;
    private array $errors = [];

    public function __construct(
        array $args = [] // Named arguments from the command line
    ) {
        // Escape the args and remove extra quotes
        $sanitizedArgs = array_map(
            fn($arg) => str_replace("'", '', escapeshellarg(trim($arg))),
            $args
        );

        $this->inputFolder = $sanitizedArgs['input'] ?? $this->inputFolder;
        $this->outputFileName = $sanitizedArgs['output'] ?? $this->outputFileName;
        $this->csvSeparator = $sanitizedArgs['separator'] ?? $this->csvSeparator;
        $this->csvEnclosure = $sanitizedArgs['enclosure'] ?? $this->csvEnclosure;

        // Here we can decide what kind of file we are parsing, XML, JSON, etc.
        // For now, we are only supporting Finvoices, but we can add more factories later for different file types and sources
        $this->invoiceFactory = new FinvoiceFactory();

        $this->invoiceDataCollection = InvoiceDataCollection::create();
    }

    /**
     * Execute the command. The command does the following:
     *
     * - Get all the suitable files from the input folder and create a collection of FileData objects
     * - Go through the FileData objects using the selected factory methods to create a collection of InvoiceData objects
     * - Sort the InvoiceData collection by payment due date
     * - Generate the CSV file from the InvoiceData collection
     * - Save the CSV file to the output file
     * - Output the summary
     */
    public function execute(): void
    {
        /**
         * Get all the suitable files from the input folder
         */
        try {
            $fileDataCollection = self::getAllValidFilesFromFolder($this->inputFolder, $this->invoiceFactory->getFileType());
        } catch (ParseFilesCommandException $e) {
            echo self::STYLE_RED . "Fatal error occurred while getting files: " . $e->getMessage() . self::STYLE_END . PHP_EOL;
            die();
        }

        // Ok, we have the files, let's start processing them
        echo self::STYLE_BOLD . "Found total of " . $fileDataCollection->count() . " files" . self::STYLE_END . PHP_EOL . PHP_EOL;
        echo "Processing..." . PHP_EOL . PHP_EOL;

        // First we have to check what factory we are using
        // For now, we only have one factory, which implements XMLInvoiceFactoryInterface
        // But we can add more factories later for example for JSON files
        if ($this->invoiceFactory instanceof XMLInvoiceFactoryInterface) {
            $this->parseXMLFiles($fileDataCollection);
        } else {
            echo self::STYLE_RED . "File type not supported yet" . self::STYLE_END . PHP_EOL;
            die();
        }

        if (!$this->invoiceDataCollection->empty()) {
            // Sort the collection descending by payment due date
            $this->invoiceDataCollection->sort(
                fn($a, $b) => $b->paymentDueDate <=> $a->paymentDueDate
            );

            // Create the CSV file
            try {
                $this->createCSVFile();
            } catch (\Exception $e) {
                echo self::STYLE_RED . "Fatal error occurred while creating the CSV file: " . $e->getMessage() . self::STYLE_END . PHP_EOL;
                die();
            }
        }

        $this->outputSummary();
    }

    /**
     * Generate the CSV file from the collected data and save it to the output file
     */
    public function createCSVFile(): void
    {
        // Generate the CSV content from the collected data
        $csvContent = (
            new GenerateCSVAction(
                $this->invoiceDataCollection,
                $this->csvSeparator,
                $this->csvEnclosure
            )
        )->execute();

        file_put_contents($this->outputFileName, $csvContent);
    }

    /**
     * Parse the XML files and create InvoiceData objects from them and add to collection
     *
     * @param FileDataCollection $files
     */
    public function parseXMLFiles(FileDataCollection $files): void
    {
        // Start collecting the data from the XML files
        foreach ($files as $file) {
            /**
             * Check if file is reasonable sized (max 1 MB) to avoid crashing, since we are reading
             * the whole file into memory at once. Processing larger files is out of scope for now.
             *
             * If later on we would need to process larger files, we have to
             * refactor the XML reader to read the file in chunks or handle it otherwise.
             */
            if (filesize($file->getFullFilePath()) > 1024 * 1024) {
                $this->errors[] = "Error: File {$file->fileName} is too large";
                continue;
            }

            try {
                $invoiceData = $this->invoiceFactory->createInvoiceFromFile($file);

                // Check if the invoice is already in the collection and skip
                if ($this->invoiceDataCollection->contains($invoiceData)) {
                    $this->skipped++;
                    continue;
                }

                $this->invoiceDataCollection->add($invoiceData);
                $this->generated++;
            } catch (\Exception $e) {
                $this->errors[] = "Error while processing file {$file->fileName}: " . $e->getMessage() . self::STYLE_END . PHP_EOL;
            }
        }
    }

    /**
     * Get all the valid files from the folder
     *
     * @param string $folder
     * @param FiletypeEnum $validFiletype
     * @return FileDataCollection
     * @throws ParseFilesCommandException
     */
    private static function getAllValidFilesFromFolder(string $folder, FiletypeEnum $validFiletype): FileDataCollection
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

        $fileDataCollection = FileDataCollection::create();
        foreach ($actualFiles as $file) {
            $fileDataCollection->add(
                new FileData(
                    filePath: $folder,
                    fileName: $file,
                    fileType: FiletypeEnum::getFromFullFilePath($folder . '/' . $file),
                )
            );
        }

        return $fileDataCollection->filter(fn($fileData) => $fileData->fileType === $validFiletype);
    }


    public function outputSummary(): void
    {
        foreach ($this->errors as $error) {
            echo self::STYLE_RED . $error . self::STYLE_END . PHP_EOL;
        }

        echo '---[Summary]-------------------------------------' . PHP_EOL;
        if ($this->generated > 0) {
            echo self::STYLE_GREEN . "Generated {$this->generated} payments and saved them to {$this->outputFileName}" . self::STYLE_END . PHP_EOL;
        } else {
            echo self::STYLE_YELLOW . "Found 0 payments to generate" . self::STYLE_END . PHP_EOL;
        }
        echo self::STYLE_YELLOW . "Skipped {$this->skipped} duplicate invoices" . self::STYLE_END . PHP_EOL;

        $errorsCount = count($this->errors);
        echo self::STYLE_RED . "Skipped {$errorsCount} invoices because errors" . self::STYLE_END . PHP_EOL;
    }
}

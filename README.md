# Invoice Parser
A command line PHP parser for extracting invoice data from different kind of files to output to a CSV file. For now supports only XML files and Finvoices.

## How it works
- Get all the suitable files from the input folder and create a collection of FileData objects
- Go through the FileData objects using the selected factory methods to create a collection of InvoiceData objects
- Sort the InvoiceData collection by payment due date
- Generate the CSV file from the InvoiceData collection
- Save the CSV file to the output file
- Output the summary

## Expanding the parser
- Add a new interface if you need to support a new file type, for example `JSONInvoiceFactoryInterface`
- Modify the `FileData` class to support the new file type
- Create a new factory class that implements the new interface, for example `PaytrailInvoiceFactory`
- Modify the `ParseFilesCommand` class to support the new factory, for example add a new option `--paytrail` and use the `PaytrailInvoiceFactory` if the option is set

## Installation

```bash
composer install
```

## Usage

```bash
php bin/parse-files
```

Will parse all XML files found the `examples` directory and output the results to a CSV file `invoices.csv` in the project root. The CSV file will be overwritten if it already exists.

### Options

Take note that you need to add extra `--` before the options, as required by Composer.

#### `--input`
The full path containing the XML files to be parsed. Defaults to `examples`.

#### `--output`
The path to the CSV file where the results will be written. Defaults to `invoices.csv`.

#### `--separator`
A custom separator for the CSV file. Defaults to `;`.

#### `--enclosure`
A character for wrapping CSV fields. Defaults to none.

### Example with options

```bash
php bin/parse-files --input=/path/to/directory --output=output_filename.csv --separator=',' --enclosure='"'
```

Will parse all XML files found in `/path/to/directory` and output the results to `/path/to/output.csv` with a comma as the separator and double quotes as the enclosure.
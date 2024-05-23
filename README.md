# finvoice-parser
A command line PHP parser for XML finvoices

The flow is as follows:
* Parses XML files found in the specified directory
* Extracts relevant data from XML
* Filters out duplicate invoices (same invoice number and supplier)
* Sorts the invoices in the descending order by due date
* Outputs the invoices to a CSV file specified by the user

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
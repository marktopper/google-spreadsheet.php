
### Out of Date!! ###

# The google API has changed, so I need to update this package 



# Google Spreadsheet Client for Laravel/PHP

Originally forked from mach3/google-spreadsheet.php to work with Laravel 5.x (and correct namespacing)

Google Spreadsheet Client for Laravel/PHP. 


## Get started

### 1. Get key file

1. Log in [Google Developper Console](https://console.developers.google.com)
2. Create new project
3. Create **Service Account** credentials in the project
4. Download key file as JSON

### 2. Create spreadsheet

1. Create a new spreadsheet in [Google Drive](https://drive.google.com)
2. Authorize the email address, which is found as "client_email" in key file, to read and edit.
3. Save the **file ID** from address bar.


### 3.1 Composer
composer require mitni/laravel-google-spreadsheet

This requires "google/apiclient" package, but that should be included by default. 

### 3.2 Access by PHP

```php
use Mitni\Google\GoogleSpreadsheet;

$client = GoogleSpreadsheet::getClient("the/path/to/credential.json");
// Get the file by file ID
$file = $client->file("XXXxxxXXXXxxxXXXX");
// Get the sheet by title
$sheet = $file->sheet("Sheet1");
// Flush all rows in the sheet
var_dump($sheet->items);
```

## Usage

### Select rows

```php
// Array
$items = $sheet->select(array("id" => "1"));
// Closure
$items = $sheet->select(function($row){
	return (int) $row["age"] < 30;
});
```

### Insert a new row

```php
$sheet->insert(array(
	"name" => "John",
	"age" => 23,
	"email" => "john@example.com"
));
```

### Update column's value

```php
$sheet->update(
	8, // row number
	"name", // field's name (or column number as Integer)
	"Tom"
);
```

### Get up-to-date table data

```php
$items = $sheet->fetch(true)->items;
```

### Save cache

```php
$client->config(array(
	"cache" => true,
	"cache_dir" => "cache",
	"cache_expires" => 3600
));
```


## Requirement

- [google/apiclient](https://github.com/google/google-api-php-client) (Apache License v2.0)


### Test excercise

This code can be run in three ways:
* command line stand alone files are in "standalone example" folder
* copied a blank Laravel project and pushed it to here, git clone etc (repo should include all files needed, even vendor folder)
* same code is a working API at converter.lipso.net

### Running as standalone files

"php json2csv.php input.json output.csv" for converting json file to csv file.

"php json2csv.php input.csv output.json" for converting csv file to json file.

There are some things presumed about csv file:
* field delimiter is ;
* text fields are escaped with " (double quotes)
* text cells would better be quoted
* sample csv file is includes for review about a working format (exported from LibreOffice Calc 7.1)

### API usage

Presuming that a work like this would, in a real world case, be a part of some other application, not a command line tool.
This small Laravel API shows both cases - classes containing converters are a part on bigger application and it is an API meant to be used by other apps.

Testing it with Postman:

POST https://converter.lipso.net/api/json2csv with JSON data as body, set header Content-Type: application/json to get CSV file contents as body

POST https://converter.lipso.net/api/csv2json with CSV file contents as body, set header Content-Type: text/plain to get JSON as response

### System requirements and other notes

Standalone command line examples should run fine with both PHP 7.3 and 7.4, not tested with any other version, but might run with anything from 7.3 (array_key_last is somewhat newer function, so 7+ is a must)

Laravel version, when cloned, should require same about PHP plus anything Laravel 8 requires. Everything relevant to this task is in app/http/controllers and app/http/workers

Please read comments from my solution files, i had to make a few assumptions and possible conversion settings (currently on my chosen default).
The main reason (problem?) being that null is not a thing for a plain text based csv file (and not for PHP JSON tools either).
So i had to make a few choices to make users notice about null values and keep something in final outputted JSON.

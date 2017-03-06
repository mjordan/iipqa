# Islandora Import Package QA Tool [![Build Status](https://travis-ci.org/mjordan/iipqa.svg?branch=master)](https://travis-ci.org/mjordan/iipqa)

A tool for applying Quality Assurance checks against Islandora import packages in preparation for importing them.

## System requirements and installation

* PHP 5.5.0 or higher.
* [Composer](https://getcomposer.org)
* `wget` (but see note below for what to do if you don't have `wget` installed).


To install the Islandora Import Package QA Tool:
* Clone the Git repo
* `cd iipqa`
* `php composer.phar install` (or equivalent on your system, e.g., `./composer install`)

> iipqa uses `wget` to retrieve some schema files from the Library of Congress on installation. If you are on a system that does not have `wget` installed and in your PATH (e.g., most Windows systems), you will see an error starting with `'wget' is not recognized as an internal or external command, operable program or batch file.` If you see this error, all you need to do is manually download the following two files into iipqa's `src/utils` directory if you want to use iipqa to validate MODS XML files:
>  * http://www.loc.gov/standards/xlink/xlink.xsd
>  * http://www.loc.gov/mods/xml.xsd

## What does iipqa check for?

* That files and directories in import packages are arranged according to how each of the batch modules expects them to be arranged.
* That directories named to indicate page order for books and newspapers issues are numeric.
* That there are no extra files like .Thumbs.db, .DS_Store, or .log files mixed in with the import packages.
* That MODS XML files in import packages validate.

The MODS validation test is optional, and is enabled by providing the `-v` option in the `iipqa` command. The other tests are always run.

## Usage

iipqa should be run against your Islandora import packages prior to loading the packages with Islandora Batch, Islandora Book Batch, Islandora Newspaper Batch, or Islandora Compound Batch. Run iipqa as follows:

`php iipqa [options] directory`

'directory' (required) is the path to the directory containing Islandora import packages you want to test. The trailing slash is optional. If you wish, you may specify the path to a Zip file instead  of a directory. The Zip file must be structured as required by Islandora Batch, Book Batch, or Newspaper Batch.

Options:

```
-m/--content_model <argument>
     Required. An aliases for groups of Islandora content models. Allowed values are single, newspapers, books, compound.

-l/--log <argument>
     Path to the log. Default is ./iipqa.log

-s/--strict
     If present, iipqa will exit with a code of 1 instead of 0 if it encounters any errors. Useful while running iipqa within shell scripts.

-v/--validate_mods
     If present, iipqa will validate all MODS XML files in all input packages.

-p/--post_iipqa <argument>
     Path to script to run after iipqa performs its tests.

--help
     Show the help page for this command.
```

When you run iipqa, like this:

```
./iipqa -m single -l ./test.txt /tmp/test
```

you will see output like this if no QA tests fail:
```
Starting QA tests...
Running test 'Unique file extensions'	########## Done.
Running test 'XML/OBJ pairs'		########## Done.
Running test 'Directories present'	########## Done.
All tests successful.
```

or like this, if any do:

```
Starting QA tests...
Running test 'Unique file extensions'	########## Done.
Running test 'XML/OBJ pairs'		########## Done.
Running test 'Directories present'	########## Done.
Some tests failed. Details are available in test.txt
```

If any of iipqa's checks failed, details of the failure will be available in your log file.

## Post-iipqa scripts

If you include the `-p` option with the path to one or more executable scripts, iipqa will run the script after it has completed all of its tests. This script can be written in any language. You can use it to add your own tests, such as checking the resolution of TIFF files or verifying the encoding of OCR files, or do things like email yourself the iipqa log file. The `scripts` directory contains some sample post-iipqa scripts.

Here are some example uses:

A single script:

`-p somescript.sh`

A single script with arguments:

`-p "somescript.sh foo bar"`

Multiple scripts, some with arguments:

`-p [somescript.php, "someotherscript.php foo bar", cleanup.py]`

Scripts with arguments must be wrapped in double quotes (`"`), and multiple script paths (and their arguments) must be separated by commas (`,`) wrapped in square brackets (`[]`) as illustrated in these examples.

The `scripts` directory contains three samples. One of them, `check_title_length.php`, performs a useful test: it checks for titles in MODS XML files that exceed Fedora Repository's limit of 255 characters for object labels, and also checks for empty mods:title elements. The other two scripts are developer examples. Running the `check_title_length.php` script would look like this:

```
php iipqa -m single -l test.log -p "scripts/get_title_length.php /tmp/input" /tmp/input
```

## License

GPLv3

## To do

* Come up with an better short name for this tool than 'iipqa'!
* Add PHPUnit tests for compound, book, and newspaper issue classes.
* Have the MODS validator provide specific error messages instead of just pass/fail
* Add developer documentation, so people can add their own QA checks and content models.

## Development/contributing

There are two ways to extend this tool so that it performs additional tests on Islandora ingest packages: 1) write a custom post-iipqa script, or 2) modify the core content-model classes.

If you want to contribute to the development of iipqa, please consider the following:

* If you discover a bug, or have a use case not documented here, open an issue.
* If you want to open a pull request, please open an issue first.
* Coding guidelines
  * Check code style with `./vendor/bin/phpcs --standard=PSR2 src`
  * Write PHPUnit tests, then run them within the iipqa directory by running `phpunit tests`

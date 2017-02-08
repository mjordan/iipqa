# Islandora Import Package QA Tool [![Build Status](https://travis-ci.org/mjordan/iipqa.svg?branch=master)](https://travis-ci.org/mjordan/iipqa)

A tool for applying Quality Assurance checks against Islandora import packages in preparation for importing them.

## System requirements and installation

* PHP 5.5.0 or higher.
* [Composer](https://getcomposer.org)

To install Islandora QA Framework:
* Clone the Git repo
* `cd iipqa`
* `php composer.phar install` (or equivalent on your system, e.g., `./composer install`)

## Usage

iipqa should be run against you Islandora import packages prior to loading the packages with Islandora Batch, Islandora Book Batch, Islandora Newspaper Batch, or Islandora Compound Batch. Run iipqa as follows:

`php iipqa [options] directory`

'directory' (required) is the path to the directory containing Islandora import packages you want to test. The trailing slash is optional.

Options:

```
-m/--content_model <argument>
     Required. An aliases for groups of Islandora content models. Allowed values are single, newspapers, books, compound.

-l/--log <argument>
     Path to the log. Default is ./iipqa.log

-s/--strict
     If present, iipqa will exit with a code of 1 instead of 0 if it encounters any errors. Useful while running iipqa within shell scripts.

--help
     Show the help page for this command.
```

When you run the iipqa, like this:

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

## License

GPLv3

## To do

* Add QA checks for newspaper issue import packages.
* Add PHPUnit tests for compound and book classes.
* Add ability to run optional QA checks, for example validating .xml files against a schema.
* Add developer documentation, so people can add their own QA checks and content models.

## Development/contributing

* If you discover a bug, or have a use case not documented here, open an issue.
* If you want to open a pull request, please open an issue first.
* Coding guidelines
  * Check code style with `./vendor/bin/phpcs --standard=PSR2 src`
  * Write PHPUnit tests, then run them within the iipqa directory by running `phpunit tests`

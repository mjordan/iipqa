# Islandora Import Package QA Tool [![Build Status](https://travis-ci.org/mjordan/iipqa.svg?branch=master)](https://travis-ci.org/mjordan/iipqa)

A tool for applying Quality Assurance checks against Islandora import packages in preparation for importing them.

## System requirements and installation

* PHP 5.5.0 or higher.
* [Composer](https://getcomposer.org)

To install Islandora QA Framework:
* Clone the Git repo
* `cd iipqa`
* `php composer.phar install` (or equivalent on your system, e.g., `./composer install`)

## What does iipqa check for?

* That files and directories in the import packages are arranged according to how each of the batch modules expects them to be arranged.
* That directories named to indicate page order for books and newspapers issues are numeric.
* That there are no extra files like .Thumbs.db, .DS_Store, or log files mixed in with the import packages.

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

-p/--post_iipqa <argument>
     Path to script to run post-iipqa.

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

## Post-iipqa scripts

If you include the `-p` option with the path to an executable script, iipqa will run the script after it has completed all of its tests. This script can be written in any language. You can use it to add your own tests, such as validating XML files. The `scripts` directory contains some sample post-iipqa scripts.

## License

GPLv3

## To do

* Add PHPUnit tests for compound, book, and newspaper issue classes.
* Add better post-iipqa sample scripts.
* Add developer documentation, so people can add their own QA checks and content models.

## Development/contributing

* If you discover a bug, or have a use case not documented here, open an issue.
* If you want to open a pull request, please open an issue first.
* Coding guidelines
  * Check code style with `./vendor/bin/phpcs --standard=PSR2 src`
  * Write PHPUnit tests, then run them within the iipqa directory by running `phpunit tests`

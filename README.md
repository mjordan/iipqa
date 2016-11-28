# Islandora Import Package QA Tool

A tool for applying Quality Assurance tests to Islandora import packages before importing them.

## System requirements and installation

* PHP 5.5.0 or higher. Not tested on PHP 7.
* [Composer](https://getcomposer.org)

To install Islandora QA Framework:
* Clone the Git repo
* `cd iipqa`
* `php composer.phar install` (or equivalent on your system, e.g., `./composer install`)

## Usage

`php iipqa [options] directory`

'directory' (required) is the path to the directory containing Islandora import packages you wan to test. The trailing slash is optional.

Options:

```
arg 0
     Required. A directory containing Islandora import packages. Trailing slash is optional.

-m/--content_model <argument>
     Required. An aliases for groups of Islandora content models. Allowed values are single, newspapers, books, compound.

-l/--log <argument>
     Path to the log. Default is ./iipqa.log

-s/--strict
     If present, iipqa will exit with a code of 1 if it encounters any errors. Useful while running iipqa within other scripts.

--help
     Show the help page for this command.
```

When you run the tests, like this:

```
./iipqa -m single -l ./test.txt /tmp/test
```

you will see output like this:
```
Starting QA tests...
Running test 'Unique file extensions'	########## Done.
Running test 'Directories present'	########## Done.
All tests successful.
```

or this:

```
Starting QA tests...
Running test 'Unique file extensions'	########## Done.
Running test 'Directories present'	########## Done.
Some tests failed. Details are available in test.txt
```

## License

GPLv3

## Development

* Check code style with `./vendor/bin/phpcs src`
* Write PHPUnit tests, then run them within /tests by running `phpunit`
* If you discover an issue, or have a use case not documented here, open an issue.

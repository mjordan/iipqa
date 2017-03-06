#!/usr/bin/env php
<?php
/**
 * @file
 * Recurses down a directory, parses out the mods:title element in found files,
 * and reports if the length of the value of that element is greater than 225 characters
 * or is 0.
 */

require 'vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$input_dir = trim($argv[1]);

$path_to_log = './iipqa_title_length.log';
$log = new Logger('title_length');
$log->pushHandler(new StreamHandler($path_to_log, Logger::WARNING));

$directory_iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($input_dir));
foreach ($directory_iterator as $filepath => $info) {
    if (preg_match('/\.xml$/', $filepath)) {
        get_title_length($filepath, $log);
    }
}

/**
 * Tests the length of the first mods:title element.
 *
 * @param string $path
 *   The path to the .xml file.
 * @param object $log
 *   The Monolog log object.
 */
function get_title_length($path, $log)
{
    $dom = new DOMDocument();
    $dom->load($path);
    $xpath = new DOMXPath($dom);
    $xpath->registerNamespace('mods', 'http://www.loc.gov/mods/v3');
    $titles = $xpath->query('//mods:titleInfo/mods:title');
    if ($titles->length > 0) {
        $title = $titles->item(0)->nodeValue;
        if (strlen($title) > 255) {
            $log->warning("Title in $path is longer than 255 characters");
        }
        if (strlen($title) === 0) {
            $log->warning("Title in $path is empty");
        }
    }
}

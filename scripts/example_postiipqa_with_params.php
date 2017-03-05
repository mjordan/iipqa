#!/usr/bin/env php
<?php

/**
 * Another post-iipqa script, this time illustrating how to use iipqa's
 * autoload.php file, so your script can access Monolog, etc.
 */

require 'vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$foo = trim($argv[1]);
$bar = trim($argv[2]);

var_dump($argv);

$path_to_my_log = '/tmp/iipqa_with_params.log';

$log = new Logger('name');
$log->pushHandler(new StreamHandler($path_to_my_log, Logger::WARNING));

$log->warning($foo);
$log->error($bar);

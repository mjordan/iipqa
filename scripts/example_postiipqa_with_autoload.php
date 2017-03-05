#!/usr/bin/env php
<?php

/**
 * Another post-iipqa script, this time illustrating how to use iipqa's
 * autoload.php file, so your script can access Monolog, etc.
 */

require 'vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$path_to_my_log = '/tmp/ippqa_autoload.lg';

$log = new Logger('name');
$log->pushHandler(new StreamHandler($path_to_my_log, Logger::WARNING));

$log->warning('Foo');
$log->error('Bar');

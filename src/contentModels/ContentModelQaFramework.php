<?php

namespace iipqa\contentmodels;

use Monolog\Logger;

/**
 *
 */
abstract class ContentModelQaFramework
{
    /**
     *
     */
    public function __construct($path_to_input_directory, $path_to_log)
    {
        // Flag that is set within tests if a path is matched.
        $this->matches = false;

        $this->inputDirectory = $path_to_input_directory;
        $this->testResults = array();

        // Set up the logger.
        $this->pathToLog = $path_to_log;
        $this->log = new \Monolog\Logger('iipqa');
        $this->logStreamHandler= new \Monolog\Handler\StreamHandler($this->pathToLog, Logger::INFO);
        $this->log->pushHandler($this->logStreamHandler);

        print "Starting QA tests...\n";
    }

    /**
     * Inspect every file and/or directory under $this->inputDirectory and apply tests.
     *
     * @param array $config
     *    The configuration data.
     * @param boolean $destructive
     *    Whether or not to perform destructive actions such as deletion.
     */
    abstract public function applyQaTests();

    /**
     * Print out a progress bar for a QA test.
     *
     * @param string $test_name
     *    The name of the current test.
     * @param int $num_paths
     *    The number of paths being tested.
     * @param int $current_path_num
     *    The position of the path being tested within the array of all paths.
     */
    public function progressBar($test_name, $num_paths, $current_path_num)
    {
        if (strlen($test_name) <= 10) {
            $num_tabs = 1;
        } else {
            $num_tabs = 2;
        }

        if (!$num_paths) {
            return;
        }

        if ($current_path_num == 1) {
            print "Running test '$test_name'" . str_repeat("\t", $num_tabs);
        }
        // @todo: Determine chunk size so that each # represents
        // 10% of the paths.
        $num_chunks = $num_paths / 10;
        if ($current_path_num % $num_chunks == 0) {
            print "#";
        }
        if ($num_paths == $current_path_num) {
            if ($this->matches) {
                print " Done." . PHP_EOL;
            } else {
                print " Did not match any paths." . PHP_EOL;
            }
        }
    }
}

<?php

namespace iipqa\utils;

use Monolog\Logger;

/**
 *
 */
class ProgressBar
{

    // Flag that is set within tests if a path is matched.
    public $matches = false;

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
        if ($num_paths < 10) {
            $num_chunks = $num_paths;
        } else {
            $num_chunks = $num_paths / 10;
        }

        if ($current_path_num % $num_chunks == 0) {
            print "#";
        }
        if ($num_paths == $current_path_num) {
            if ($this->matches) {
                print " Done." . PHP_EOL;
            } else {
                print PHP_EOL;
            }
        }
    }
}

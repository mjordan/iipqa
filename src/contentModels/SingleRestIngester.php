<?php

namespace iipqa\contentmodels;

/**
 *
 */
class SingleRestIngester extends ContentModelQaFramework
{
    /**
     * @param string $path_to_input_directory
     *    The path to the input directory, from qa.
     * @param string $path_to_log
     *    The path to the log file, from qa.
     * @param object $command
     *    The command used to invoke iipqa.
     */
    public function __construct($path_to_input_directory, $path_to_log, $command)
    {
        parent::__construct($path_to_input_directory, $path_to_log, $command);
        $this->contentModelAlias = 'single_rest_ingester';

        $this->reader = new \iipqa\utils\Reader();
        $this->progressBar = new \iipqa\utils\ProgressBar();
    }

    /**
     * @return array
     *    An array of test results, containing true and false.
     */
    public function applyQaTests()
    {

        $this->testResults[] = $this->checkForNonDirectories();
        $this->testResults[] = $this->checkRequiredSingleRestFiles();

        return $this->testResults;
    }

    /**
     * Checks to make sure that there are only directories in the input directory.
     *
     * @return bool
     *    True if the test passes, false if it doesn't.
     */
    public function checkForNonDirectories()
    {
        // Unlike in the standard Single test class, we use separate readers
        // to get the list of paths to check.
        $this->singleObjectPathsToTest = $this->reader->read($this->inputDirectory);

        $files_present = array();
        $this->numSingleObjectPathsToTest = count($this->singleObjectPathsToTest);
        $current_path_num = 0;
        foreach ($this->singleObjectPathsToTest as $path) {
            $current_path_num++;
            $this->matches = true;
            // To skip .. and .
            if (!preg_match('#\.{1,2}$#', $path)) {
                $this->progressBar->progressBar(
                    'Check for nondirectories',
                    $this->numSingleObjectPathsToTest,
                    $current_path_num
                );
                if (!is_dir($path)) {
                    $this->log->addWarning("File present " . $path);
                    $files_present[] = $path;
                }
            }
        }

        // If there are no files in the input directory, we're good to go.
        if (count($files_present)) {
            return false;
        } else {
            $this->log->addInfo("'Check for nondirectories' test did not find any problems.");
            return true;
        }
    }

    /**
     * Checks to make sure that each compond object directory contains only a file
     * with the name 'OBJ' (any extension) and a file named 'MODS.xml'.
     *
     * @return bool
     *    True if the test passes, false if it doesn't.
     */
    public function checkRequiredSingleRestFiles()
    {
        // We already have $this->singleObjectPathsToTest and $this->numSingleObjectPathsToTest
        // so there's no need to regenerate them.

        $bad_object_paths = array();
        $current_path_num = 0;
        foreach ($this->singleObjectPathsToTest as $path) {
            $current_path_num++;
            $this->matches = true;

            // To skip .. and .
            if (!preg_match('#\.{1,2}$#', $path)) {
                $this->progressBar->progressBar(
                    'Check for required single (REST) object files',
                    $this->numSingleObjectPathsToTest,
                    $current_path_num
                );

                $files_in_object_dir = $this->reader->read($path);
                if (count($files_in_object_dir) > 2) {
                    $this->log->addWarning("Check required single (REST) object files - file/directory count in " . $path . " is greater than 2");
                    $bad_object_paths[] = $path;
                }
                foreach ($files_in_object_dir as $file_in_object_dir) {
                    if (is_dir($file_in_object_dir)) {
                        $this->log->addWarning("Check required single (REST) object files - unwanted subdirectory is present in " . $path);
                        $bad_object_paths[] = $path;
                    }

                    // Get OBJ file path.
                    $pathinfo = pathinfo($file_in_object_dir);
                    // We can use simple two-check logic since we're only
                    // looking for two files.
                    if (!preg_match('/^MODS\.xml/', $pathinfo['basename'])) {
                        if (!preg_match('/^OBJ\./', $pathinfo['basename'])) {
                            $this->log->addWarning("Check required single (REST) object files - OBJ file is missing from " . $path);
                            $bad_object_paths[] = $path;
                        }
                    }

                }

            }
        }

        // If there are no reported bad paths, we're good to go.
        if (count($bad_object_paths)) {
            return false;
        } else {
            $this->log->addInfo("'Check for required single (REST) object files' test did not find any problems.");
            return true;
        }
    }
}

<?php

namespace iipqa\contentmodels;

/**
 *
 */
class Single extends ContentModelQaFramework
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
        $this->contentModelAlias = 'single';

        // Since all of our tests use the same list of paths, we can generate the
        // list in the constructor to avoid generating it within each test.
        $reader = new \iipqa\utils\Reader();
        $this->pathsToTest = $reader->read($this->inputDirectory);
        $this->numPathsToTest = count($this->pathsToTest);

        $this->progressBar = new \iipqa\utils\ProgressBar();
    }

    /**
     * @return array
     *    An array of test results, containing true and false.
     */
    public function applyQaTests()
    {
        $this->testResults[] = $this->checkExtensions();
        $this->testResults[] = $this->checkFilePairs();
        $this->testResults[] = $this->checkForDirectories();
        return $this->testResults;
    }

    /**
     * Checks to make sure that there are only two file extensions, .xml
     * and one other. This assumes that all the OBJ datastreams being loaded
     * will have the same extension.
     *
     * @return bool
     *    True if the test passes, false if it doesn't.
     */
    public function checkExtensions()
    {
        $all_files = glob($this->inputDirectory . DIRECTORY_SEPARATOR . '*');
        $only_dirs = array();
        foreach ($all_files as $file) {
            if (is_file($file)) {
                $only_files[] = $file;
            }
        }
        
        $num_files = count($only_files);

        $extensions = array();
        $current_path_num = 0;
        foreach ($only_files as $path) {
            // The next two lines should always be placed directly after the foreach()
            // loop through the paths being checked.
            $this->progressBar->matches = true;
            $current_path_num++;
            $this->progressBar->progressBar('Unique file extensions', $num_files, $current_path_num);
            $pathinfo = pathinfo($path);
            // To account for files with no extension.
            if (isset($pathinfo['extension'])) {
                $extensions[] = strtolower($pathinfo['extension']);
            } else {
                $extensions[] = '';
            }
        }
        $unique_extensions = array_unique($extensions);
        if (count($unique_extensions) !== 2) {
            $this->log->addWarning("Unique extensions " . var_export($unique_extensions, true));
            return false;
        } else {
            $this->log->addInfo("'Unique file extensions' test did not find any problems.");
            return true;
        }
    }

    /**
     * Checks to make sure that each .xml file has a corresponding OBJ file
     * and vice versa.
     *
     * @return bool
     *    True if the test passes, false if it doesn't.
     */
    public function checkFilePairs()
    {
        // Since this test doesn't iterate through $this->pathsToTest, we invoke the
        // progress bar in its own little loop through that array, so this test produces
        // output like the others.
        $current_path_num = 0;
        foreach ($this->pathsToTest as $path) {
            $current_path_num++;
            $this->matches = true;
            $this->progressBar->progressBar('XML/OBJ pairs', $this->numPathsToTest, $current_path_num);
        }

        $xml_files = glob($this->inputDirectory . DIRECTORY_SEPARATOR . '*.xml');
        $xml_files_assoc = array();
        foreach ($xml_files as $xml_file) {
            $xml_pathinfo = pathinfo($xml_file);
            $xml_filename = $xml_pathinfo['filename'];
            $xml_files_assoc[$xml_filename] = $xml_pathinfo['basename'];
        }
        $obj_files = preg_grep('/\.xml$/', glob($this->inputDirectory . DIRECTORY_SEPARATOR . '*'), PREG_GREP_INVERT);
        $obj_files_assoc = array();
        foreach ($obj_files as $obj_file) {
            $obj_pathinfo = pathinfo($obj_file);
            $obj_filename = $obj_pathinfo['filename'];
            $obj_files_assoc[$obj_filename] = $obj_pathinfo['basename'];
        }
        $xml_diff = array_diff(array_keys($xml_files_assoc), array_keys($obj_files_assoc));
        $obj_diff = array_diff(array_keys($obj_files_assoc), array_keys($xml_files_assoc));

        if (count($xml_diff) || count($obj_diff)) {
            if (count($xml_diff)) {
                $this->log->addWarning("Some XML files have no corresponding OBJ file " . var_export($xml_diff, true));
            }
            if (count($obj_diff)) {
                $this->log->addWarning("Some OBJ files have no corresponding XML file " . var_export($obj_diff, true));
            }
            return false;
        } else {
            $this->log->addInfo("'XML/OBJ pairs' test did not find any problems.");
            return true;
        }
    }

    /**
     * Checks to make sure that there are no directories in the input directory.
     *
     * @return bool
     *    True if the test passes, false if it doesn't.
     */
    public function checkForDirectories()
    {
        $directories_present = array();
        $current_path_num = 0;
        foreach ($this->pathsToTest as $path) {
            // The next two lines should always be placed directly after the foreach()
            // loop through the paths.
            $current_path_num++;
            $this->matches = true;
            // To skip .. and .
            if (!preg_match('#\.{1,2}$#', $path)) {
                $this->progressBar->progressBar('Directories present', $this->numPathsToTest, $current_path_num);
                if (is_dir($path)) {
                    $this->log->addWarning("Directory present: " . $path);
                    $directories_present[] = $path;
                }
            }
        }
        if (count($directories_present)) {
            return false;
        } else {
            $this->log->addInfo("'Directories present' test did not find any problems.");
            return true;
        }
    }
}

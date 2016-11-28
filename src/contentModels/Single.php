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
     */
    public function __construct($path_to_input_directory, $path_to_log)
    {
        parent::__construct($path_to_input_directory, $path_to_log);
    	$this->contentModelAlias = 'single';

        // Since all of our tests use the same list of paths, we can generate the
        // list in the constructor to avoid generating it within each test.
        $reader = new \islandoraqa\utils\Reader();
        $this->pathsToTest = $reader->read($this->inputDirectory);
        $this->numPathsToTest = count($this->pathsToTest);
    }

    /**
     * @return array
     *    An array of test results, containing true and false.
     */
    public function applyQaTests()
    {
        $this->testResults[] = $this->checkExtensions();
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
         $extensions = array();
         $current_path_num = 0;
         foreach ($this->pathsToTest as $path) {
             // The next two lines should always be placed directly after the foreach()
             // loop through the paths.
             $current_path_num++;
             $this->matches = true;
             if (is_file($path)) {
                 $this->progressBar('Unique file extensions', $this->numPathsToTest, $current_path_num);
                 $pathinfo = pathinfo($path);
                 $extensions[] = strtolower($pathinfo['extension']);
             }
         }
         $unique_extensions = array_unique($extensions);
         if (count($unique_extensions) !== 2) {
             $this->log->addWarning("Unique extensions " . var_export($unique_extensions, true));
             return false;
         }
         return true;
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
                $this->progressBar('Directories present', $this->numPathsToTest, $current_path_num);
                 if (is_dir($path)) {
                     $this->log->addWarning("Directory present " . $path);
                     $directories_present[] = $path;
                 }
             }
         }
         if (count($directories_present)) {
             return false;
         }
         else {
             return true;
         }
     }
}

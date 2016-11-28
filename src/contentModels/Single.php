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
    public function __construct($path_to_input_directory, $strict, $path_to_log)
    {
        parent::__construct($path_to_input_directory, $strict, $path_to_log);
    	$this->contentModelAlias = 'single';

        $reader = new \islandoraqa\utils\Reader();
        $this->pathsToTest = $reader->readRecursive($this->inputDirectory);
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
             $current_path_num++;
             if (is_file($path)) {
                 $this->matches = true;
                 $this->progressBar('Unique file extensions', $this->numPathsToTest, $current_path_num);
                 $pathinfo = pathinfo($path);
                 $extensions[] = strtolower($pathinfo['extension']);
             }
         }
         $unique_extensions = array_unique($extensions);
         if (count($unique_extensions) !== 2) {
             $this->log->addWarning("Unique extensions " . var_export($unique_extensions, true));
             if ($this->strict){
                 exit(1);
             }
             else {
                 return false;
             }
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
            $current_path_num++;
            // To skip .. and .
            if (!preg_match('#\.{1,2}$#', $path)) {
                $this->progressBar('Directories present', $this->numPathsToTest, $current_path_num);
                 if (is_dir($path)) {
                     $this->matches = true;
                     $this->log->addWarning("Directory present " . $path);
                     $directories_present[] = $path;
                     if ($this->strict){
                         exit(1);
                     }
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

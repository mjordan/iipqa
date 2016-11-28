<?php

namespace iipqa\contentmodels;

/**
 * Checks for the existense of subfolders in $options['dir'] (that correspond to compound
 * objects) that contain one subfolder per child object. Each compound folder must contain
 * a file named 'structure.xml' and a file named 'MODS.xml'. Each child folder must contain
 * a named 'MODS.xml' and a file with the name 'OBJ', with any extension.
 */
class Compound extends ContentModelQaFramework
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
     * Checks to make sure that there are only directories in the input directory.
     *
     * @return bool
     *    True if the test passes, false if it doesn't.
     */
     public function checkForNonDirectories()
     {
         $files_present = array();
         $current_path_num = 0;
         foreach ($this->pathsToTest as $path) {
            $current_path_num++;
            // To skip .. and .
            if (!preg_match('#\.{1,2}$#', $path)) {
                $this->progressBar('Files present', $this->numPathsToTest, $current_path_num);
                 if (!is_dir($path)) {
                     $this->matches = true;
                     $this->log->addWarning("File present " . $path);
                     $files_present[] = $path;
                     if ($this->strict){
                         exit(1);
                     }
                 }
             }
         }
         if (count($files_present)) {
             return false;
         }
         else {
             return true;
         }
     }
}

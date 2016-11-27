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
    }

    /**
     * @return array
     *    An array of test results, containing true and false.
     */
    public function applyQaTests()
    {
        $this->testResults[] = $this->checkExtensions();
        return $this->testResults;
    }

   /**
    * Checks to make sure that there are only two file extensions, .xml
    * and one other. This assumes that all the OBJ datastreams being loaded
    * will have the same extension.
    */
    public function checkExtensions()
    {
    	$reader = new \islandoraqa\utils\Reader();
    	$this->pathsToTest = $reader->readRecursive($this->inputDirectory);
    	$numPaths = count($this->pathsToTest);

        $extensions = array();
        $current_path_num = 0;
        foreach ($this->pathsToTest as $path) {
            if (is_file($path)) {
                $current_path_num++;
                $this->progressBar('Unique file extensions', $numPaths, $current_path_num);
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
    }
}

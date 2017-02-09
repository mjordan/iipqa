<?php

namespace iipqa\contentmodels;

/**
 * Checks for input for the Islandora Compound Batch module.
 */
class Compound extends ContentModelQaFramework
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
        $this->testResults[] = $this->checkRequiredCompoundFiles();
        $this->testResults[] = $this->checkStructure();
        $this->testResults[] = $this->checkRequiredChildFiles();
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
        // Unlike in the Single test class, we use separate readers
        // to get the list of paths to check.
        $this->compoundPathsToTest = $this->reader->read($this->inputDirectory);

        $files_present = array();
        $this->numCompoundPathsToTest = count($this->compoundPathsToTest);
        $current_path_num = 0;
        foreach ($this->compoundPathsToTest as $path) {
            $current_path_num++;
            $this->matches = true;
            // To skip .. and .
            if (!preg_match('#\.{1,2}$#', $path)) {
                $this->progressBar->progressBar(
                    'Check for nondirectories',
                    $this->numCompoundPathsToTest,
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
            return true;
        }
    }

    /**
     * Checks to make sure that each compond object directory contains only a file
     * named 'structure.xml' and a file named 'MODS.xml'.
     *
     * @return bool
     *    True if the test passes, false if it doesn't.
     */
    public function checkRequiredCompoundFiles()
    {
         // We already have $this->compoundPathsToTest and $this->numCompoundPathstoTest
         // so there's no need to regenerate them.

        $bad_compound_paths = array();
        $current_path_num = 0;
        foreach ($this->compoundPathsToTest as $path) {
            $current_path_num++;
            $this->matches = true;
            // To skip .. and .
            if (!preg_match('#\.{1,2}$#', $path)) {
                $this->progressBar->progressBar(
                    'Check for required compound object files',
                    $this->numCompoundPathsToTest,
                    $current_path_num
                );
                // Get all files in the compound object directory. The only ones that
                // should be present are MODS.xml and structure.xml.
                $files_in_compound_dir = $this->reader->read($path);
                $wanted_files = array('MODS.xml', 'structure.xml');
                foreach ($files_in_compound_dir as $file_in_compound_dir) {
                    if (is_dir($file_in_compound_dir)) {
                        continue;
                    }
                    $basename = basename($file_in_compound_dir);
                    if (!in_array($basename, $wanted_files)) {
                        $this->log->addWarning("Check required compound object files - Unwanted file in " .
                            $path . ": " . $basename);
                        $bad_compound_paths[] = $file_in_compound_dir;
                    }
                }

                $structure_path = $path . DIRECTORY_SEPARATOR . 'structure.xml';
                if (!file_exists($structure_path)) {
                    $this->log->addWarning("Check required compound object files - structure.xml missing in " . $path);
                    $bad_compound_paths[] = $path;
                }
                $mods_path = $path . DIRECTORY_SEPARATOR . 'MODS.xml';
                if (!file_exists($mods_path)) {
                    $this->log->addWarning("Check required compound object files - MODS.xml missing in " . $path);
                    $bad_compound_paths[] = $path;
                }
            }
        }

        // If there are no reported bad paths, we're good to go.
        if (count($bad_compound_paths)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Checks to make sure that the directories named in structure.xml
     * match the directories within the compound directory.
     *
     * @return bool
     *    True if the test passes, false if it doesn't.
     */
    public function checkStructure()
    {
         // We already have $this->compoundPathsToTest and $this->numCompoundPathstoTest
         // so there's no need to regenerate them.

        $bad_compound_paths = array();
        $current_path_num = 0;
        foreach ($this->compoundPathsToTest as $path) {
            $current_path_num++;
            $this->matches = true;
            // To skip .. and .
            if (!preg_match('#\.{1,2}$#', $path)) {
                $this->progressBar->progressBar(
                    'Check for directories named in structure.xml',
                    $this->numCompoundPathsToTest,
                    $current_path_num
                );

                $subdirs_in_compound_dir = $this->reader->read($path, true);

                $structure_path = $path . DIRECTORY_SEPARATOR . 'structure.xml';
                if (file_exists($structure_path)) {
                    $structure_file_contents = file_get_contents($structure_path);
                    $xml = simplexml_load_file($structure_path);
                    $num_subdirectories = count($subdirs_in_compound_dir);
                    $num_children_in_structure_file = count($xml->child);
                    if ($num_subdirectories != $num_children_in_structure_file) {
                        $message = "Check stucture - number of child directories in directory " .
                            "($num_subdirectories) does not match number in structure.xml " .
                            "($num_children_in_structure_file) in $path";
                        $this->log->addWarning($message);
                        $bad_compound_paths[] = $path;
                    }
                    foreach ($xml->child as $child) {
                        $dir_to_check = rtrim($this->inputDirectory, DIRECTORY_SEPARATOR) .
                            DIRECTORY_SEPARATOR . $child['content'];
                        if (!file_exists($dir_to_check)) {
                            $message = "Check stucture - directory named in structure.xml " .
                                "($dir_to_check) is not present in compound object directory " .
                                "in $path";
                            $this->log->addWarning($message);
                            $bad_compound_paths[] = $path;
                        }
                    }
                } else {
                    $this->log->addWarning("Check stucture - structure.xml missing in " . $path);
                    $bad_compound_paths[] = $path;
                }
            }
        }

        // If there are no reported bad paths, we're good to go.
        if (count($bad_compound_paths)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Checks to make sure that each child object directory contains only a file
     * named 'OBJ', with any extension, and a file named 'MODS.xml'.
     *
     * @return bool
     *    True if the test passes, false if it doesn't.
     */
    public function checkRequiredChildFiles()
    {
        // We already have $this->compoundPathsToTest and $this->numCompoundPathstoTest
        // so there's no need to regenerate them.

        $bad_child_paths = array();
        $current_path_num = 0;
        foreach ($this->compoundPathsToTest as $path) {
            $current_path_num++;
            $this->matches = true;
            // To skip .. and .
            if (!preg_match('#\.{1,2}$#', $path)) {
                $this->progressBar->progressBar(
                    'Check for required child object files',
                    $this->numCompoundPathsToTest,
                    $current_path_num
                );
                // Get each child directory under the compound directory
                $child_dirs = $this->reader->read($path, true);
                foreach ($child_dirs as $child_dir_path) {
                    // Check to see if MODS.xml is present.
                    $mods_path = $child_dir_path . DIRECTORY_SEPARATOR . 'MODS.xml';
                    if (!file_exists($mods_path)) {
                        $this->log->addWarning(
                            "Check required child object files - MODS.xml missing in " .
                            $child_dir_path
                        );
                        $bad_childd_paths[] = $child_dir_path;
                    }
                    // Check to see if OBJ.something is present.
                    $pattern = $child_dir_path . DIRECTORY_SEPARATOR . "OBJ.*";
                    $obj_file_list = glob($pattern);
                    if (count($obj_file_list) < 1) {
                        $this->log->addWarning(
                            "Check required child object files - OBJ file missing in " .
                            $child_dir_path
                        );
                        $bad_child_paths[] = $child_dir_path;
                    }
                    // Get all files in the child object directory.
                    $files_in_child_dir = $this->reader->read($child_dir_path);
                    // Check to see if there are any unwanted files. The 2 that are expected, MODS.xml
                    // and OBJ.something, will have been accounted for already.
                    if (count($files_in_child_dir) > 2) {
                        $this->log->addWarning("Check required child object files - Unwanted file in " .
                            $path . ". All files in that directory: " . var_export($files_in_child_dir, true));
                        $bad_child_paths[] = $file_in_child_dir;
                    }
                }
            }
        }

        // If there are no reported bad paths, we're good to go.
        if (count($bad_child_paths)) {
            return false;
        } else {
            return true;
        }
    }
}

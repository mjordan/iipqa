<?php

namespace iipqa\contentmodels;

/**
 * Checks for input for the Islandora Book Batch module.
 */
class Books extends ContentModelQaFramework
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
        $this->contentModelAlias = 'books';

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
        $this->testResults[] = $this->checkBookFiles();
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
        $this->bookPathsToTest = $this->reader->read($this->inputDirectory);

        $files_present = array();
        $this->numBookPathsToTest = count($this->bookPathsToTest);
        $current_path_num = 0;
        foreach ($this->bookPathsToTest as $path) {
            $current_path_num++;
            $this->matches = true;
            // Skip .. and .
            if (!preg_match('#\.{1,2}$#', $path)) {
                $this->progressBar->progressBar(
                    'Check input directory for disallowed files',
                    $this->numBookPathsToTest,
                    $current_path_num
                );
                if (!is_dir($path)) {
                    $this->log->addWarning("File " . $path . " is not allowed in the input directory.");
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
     * Checks to make sure that there are only numeric subdirectories and optionally
     * a metadata file and a thumnbail file in each book directory.
     *
     * @todo: Check to make sure there is only one file of each type?
     *
     * @return bool
     *    True if the test passes, false if it doesn't.
     */
    public function checkBookFiles()
    {
        $allowed_metadata_files = array('MODS.xml', 'DC.xml', '--METADATA--.xml');
        $allowed_thumbnail_files = array('TN.jpg', 'TN.png');
        $failures = array();
        $one_file = false;

        $this->bookPathsToTest = $this->reader->read($this->inputDirectory, true);
        $this->numBookPathsToTest = count($this->bookPathsToTest);
        $current_path_num = 0;
        foreach ($this->bookPathsToTest as $book_path) {
            $current_path_num++;
            $this->matches = true;
            $files_in_book_dir = array();
            // To skip .. and .
            if (!preg_match('#\.{1,2}$#', $book_path)) {
                $this->progressBar->progressBar(
                    'Check for expected book metadata files, page subdirectories, and page files',
                    $this->numBookPathsToTest,
                    $current_path_num
                );
                if (is_dir($book_path)) {
                    // Get all subdirs, then loop through them and
                    // check if they are numeric.
                    $page_dirs = $this->reader->read($book_path);
                    foreach ($page_dirs as $page_dir) {
                        if (is_dir($page_dir)) {
                            $page_dir_segment = basename($page_dir);
                            if (!is_numeric($page_dir_segment)) {
                                $this->log->addWarning("Page direcotry $page_dir is not numeric.");
                                $failures[] = $page_dir;
                            }
                            // Check page directories for allowed files.
                            if (!$this->checkPageDirectoryContents($page_dir)) {
                                $failures[] = $page_dir;
                            }
                        } else {
                            // If a file and not a directory, check to see if it's in the
                            // list of allowed files.
                            $filename = basename($page_dir);
                            if (!in_array($filename, $allowed_metadata_files) &&
                                !in_array($filename, $allowed_thumbnail_files)) {
                                $this->log->addWarning("File $filename is not an allowed " .
                                    "filename within book directory $book_path.");
                                $failures[] = $page_dir;
                            }
                        }
                    }
                }
            }
        }

        // If there are no reported bad directory names/files, return true.
        if (count($failures)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Checks to make sure that page directories contain an OBJ file and only
     * files from an allowed list.
     *
     * @param string $dir
     *    A path to a page-level directory.
     *
     * @return bool
     *    True if the test passes, false if it doesn't.
     */
    public function checkPageDirectoryContents($dir)
    {
        $failures = array();
        $allowed_obj_files = array('OBJ.tif', 'OBJ.tiff', 'OBJ.jp2', 'OBJ.jpg', 'OBJ.jpeg', 'TECHMD.xml');
        $obj_files = glob("$dir/OBJ.*");
        if (count($obj_files) > 1) {
            $this->log->addWarning("Too many OBJ.* files in page directory $dir");
            $failures[] = $dir;
        } else {
            $obj_file = basename($obj_files[0]);
            if (!in_array($obj_file, $allowed_obj_files)) {
                $this->log->addWarning("OJB file $obj_file not in list of allowed files.");
                $failures[] = $dir;
            }
        }

        // Optional page datastream files.
        $allowed_ds_files = array(
            'MODS.xml',
            'DC.xml',
            'JP2.jp2',
            'JPEG.jpg',
            'TN.jpg',
            'TN.png',
            'OCR.asc',
            'OCR.txt',
            'HOCR.shtml'
        );
        $page_files = $this->reader->read($dir);
        foreach ($page_files as $page_file) {
            if (!preg_match('#\.{1,2}$#', $page_file)) {
                // Test to see if it's a directory (not allowed).
                if (is_dir($page_file)) {
                    $this->log->addWarning("Directory $page_file is not an allowed within page directory $dir.");
                    $failures[] = $dir;
                } else {
                    // Test to see if it's not in the list of allowed datastream files.
                    $file = basename($page_file);
                    if (!in_array($file, $allowed_ds_files) && !preg_match('/^OBJ.(tif|tiff|jp2|jpg|jpeg)/', $file)) {
                        $this->log->addWarning("File $file is not allowed within page directory $dir");
                        $failures[] = $dir;
                    }
                }
            }
        }

        if (count($failures)) {
            return false;
        } else {
            return true;
        }
    }
}

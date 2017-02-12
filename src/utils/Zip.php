<?php

namespace iipqa\utils;

use Monolog\Logger;

/**
 * Class for unzipping a .zip file and validating its internal structure.
 */
class Zip
{
    public function __construct($path_to_input_directory, $path_to_log, $command)
    {
        $this->inputDirectory = $path_to_input_directory;
        $this->ZipInputPath = $command[0];

        // Set up the logger.
        $this->pathToLog = $path_to_log;
        $this->log = new \Monolog\Logger('iipqa');
        $this->logStreamHandler= new \Monolog\Handler\StreamHandler($this->pathToLog, Logger::INFO);
        $this->log->pushHandler($this->logStreamHandler);

        $this->cmodel = $command['content_model'];
    }

    /**
     * Unzip the archive.
     *
     * @param object $command
     *    The command passed to iipqa.
     *
     * @return string|boolean
     *    The path to the unzipped packages, or false if the .zip structure
     *    is invalid.
     */
    public function unzip()
    {
        $pathinfo = pathinfo($this->ZipInputPath);
        $unzip_dir_path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $pathinfo['filename'] . '_iipqaunzip';
        if (file_exists($unzip_dir_path)) {
            $message = "Temporary directory for unzipping import packages (" . $unzip_dir_path .
                ") already exists.";
            $this->log->addWarning($message);
            print $message . PHP_EOL;
            exit(1);
        }

        $zip = new \ZipArchive;
        if ($zip->open($this->ZipInputPath) === true) {
            $zip->extractTo($unzip_dir_path);
            $zip->close();
            print $this->ZipInputPath . " unzipped...\n";
        } else {
            print "Could not unzip " . $this->ZipInputPath . "\n";
            $this->log->addWarning("Could not unzip " . $this->ZipInputPath);
            exit(1);
        }

        if ($this->validateStructure($unzip_dir_path)) {
            return $unzip_dir_path;
        } else {
            $this->log->addWarning("Invalid top-level zip structure for content model " .
                $this->cmodel . " in " . $this->ZipInputPath);
            return false;
        }
    }

    /**
     * Validate the top-level structure of the .zip.
     *
     * @param string $root_path
     *    The path to the root of the zip.
     *
     * @return boolean
     *    Whether the directory structure validates or not.
     */
    public function validateStructure($root_path)
    {
        // Islandora batch importers for the following content models require that:
        //     Single: all files must be immediate children of the zip root.
        //     Book: each book folder is an immediate child of the zip root.
        //     Newspaper: each newspaper issue folder is an immediate child of the zip root.
        if ($this->cmodel == 'single') {
            // Regular files only, directories are not allowed.
            $valid = true;
            $all_files = glob($root_path . DIRECTORY_SEPARATOR . '*');
            foreach ($all_files as $file) {
                if (is_dir($file)) {
                    $valid = false;
                }
            }
            return $valid;
        } else {
            // Directories only, regular files are not allowed.
            $valid = true;
            $all_files = glob($root_path . DIRECTORY_SEPARATOR . '*');
            foreach ($all_files as $file) {
                if (is_file($file)) {
                    $valid = false;
                }
            }
            return $valid;
        }
    }
}

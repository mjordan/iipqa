<?php

namespace iipqa\utils;

use Monolog\Logger;

class ModsValidator
{
    public function __construct($path_to_input_directory, $path_to_log, $command)
    {
        $this->inputDirectory = $path_to_input_directory;
        // We use a modified version of the MODS schema to eliminate calls
        // to the Library of Congress' website.
        $this->pathToSchema = __DIR__ . DIRECTORY_SEPARATOR . 'mods-3-5-local.xsd';

        // Set up the logger.
        $this->pathToLog = $path_to_log;
        $this->log = new \Monolog\Logger('iipqa');
        $this->logStreamHandler= new \Monolog\Handler\StreamHandler($this->pathToLog, Logger::INFO);
        $this->log->pushHandler($this->logStreamHandler);

        $this->progressBar = new \iipqa\utils\ProgressBar();
    }

    /**
     * @param string $dir
     *    The input path.
     */
    public function validateMods()
    {
        $mods_paths = array();
        $directory_iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->inputDirectory));
        foreach ($directory_iterator as $file_path => $info) {
            if (preg_match('/\.xml/', $file_path)) {
                $mods_paths[] = $file_path;
            }
        }

        $invalids_present = array();
        $current_path_num = 0;
        $num_paths = count($mods_paths);
        foreach ($mods_paths as $mods_path) {
            $current_path_num++;
            $this->progressBar->matches = true;
            $this->progressBar->progressBar('Validating MODS XML files', $num_paths, $current_path_num);
            if (!$this->validateSingleMods($mods_path)) {
                $invalids_present[] = false;
            }
        }

        if (!in_array(false, $invalids_present)) {
            $this->log->addInfo("'Validating MODS XML files' test did not find any problems.");
        }
    }

    /**
     * Validate the MODS file against the schema.
     *
     * @param string $path_to_mods
     *   The path to the MODS file to validate.
     *
     * @return boolean
     *   True on successful validation, false on failure.
     */
    public function validateSingleMods($path_to_mods)
    {
        static $schema_xml = null;
        if ($schema_xml == null) {
            $schema_xml = file_get_contents($this->pathToSchema);
        }
        $mods = new \DOMDocument();
        $mods->load($path_to_mods);
        if ($mods->schemaValidateSource($schema_xml)) {
            return true;
        } else {
            $this->log->addWarning("MODS file $path_to_mods does not validate.");
            return false;
        }
    }
}

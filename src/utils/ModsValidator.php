<?php

namespace islandoraqa\utils;

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
    }

    /**
     * @param string $dir
     *    The input path.
     */
    public function validateMods()
    {
        $directory_iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->inputDirectory));
        foreach ($directory_iterator as $filepath => $info) {
            $filename = pathinfo($filepath, PATHINFO_FILENAME);
            if (preg_match('/\.xml/', $filepath)) {
                $this->validateSingleMods($filepath);
            }
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
    public function validateSingleMods($path_to_mods) {
        static $schema_xml = null;
        if ($schema_xml == null) {
            $schema_xml = file_get_contents($this->pathToSchema);
        }
        $mods = new \DOMDocument();
        $mods->load($path_to_mods);
        if (@$mods->schemaValidateSource($schema_xml)) {
            return true;
        }
        else {
            $this->log->addWarning("MODS file $path_to_mods does not validate.");
            return false;
        }
    }

}

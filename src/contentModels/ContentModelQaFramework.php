<?php

namespace iipqa\contentmodels;

use Monolog\Logger;

/**
 *
 */
abstract class ContentModelQaFramework
{

    /**
     *
     */
    public function __construct($path_to_input_directory, $path_to_log, $command)
    {
        // Flag that is set within tests if a path is matched.
        $this->matches = false;

        $this->inputDirectory = $path_to_input_directory;
        $this->testResults = array();

        // Set up the logger.
        $this->pathToLog = $path_to_log;
        $this->log = new \Monolog\Logger('iipqa');
        $this->logStreamHandler= new \Monolog\Handler\StreamHandler($this->pathToLog, Logger::INFO);
        $this->log->pushHandler($this->logStreamHandler);

        print "Starting QA tests...\n";

        $start_time = date("F j, Y, g:i a");
        $this->log->addInfo("Configuration", array('Start time' => $start_time));
        $this->log->addInfo("Configuration", array('Strict' => $command['strict']));
        $this->log->addInfo("Configuration", array('Log file' => $command['log']));
        $this->log->addInfo("Configuration", array('Input directory' => $command[0]));
    }

    /**
     * Inspect every file and/or directory under $this->inputDirectory and apply tests.
     *
     * @param array $config
     *    The configuration data.
     * @param boolean $destructive
     *    Whether or not to perform destructive actions such as deletion.
     */
    abstract public function applyQaTests();
}

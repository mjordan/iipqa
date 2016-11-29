<?php

namespace iipqa\contentmodels;

/**
 *
 */
class Books extends ContentModelQaFramework
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
        $this->contentModelAlias = 'books';
    }

    /**
     * @return array
     *    An array of test results, containing true and false.
     */
    public function applyQaTests()
    {
        print "Sorry, the " . $this->contentModelAlias . " QA test class is not available yet.\n";
        return array('false');
    }
}

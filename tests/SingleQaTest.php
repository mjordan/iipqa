<?php

require_once 'vendor/autoload.php';

class SingleQaTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->inputDirectory = dirname(__FILE__) . '/fixtures/single/pass';
        $this->pathToLog = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "iipqa_tests.log";
    }

    public function testRunSuccessfulQA()
    {
        $single = new \iipqa\contentmodels\Single($this->inputDirectory, $this->pathToLog);
        $this->assertContains(true, $single->applyQaTests());
    }
    
    protected function tearDown()
    {
        @unlink($this->path_to_log);
    }
}

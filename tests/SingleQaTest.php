<?php

require dirname(__DIR__) . '/vendor/autoload.php';

class SingleQaTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->inputDirectoryPass = dirname(__FILE__) . '/fixtures/single/pass';
        $this->inputDirectoryFail = dirname(__FILE__) . '/fixtures/single/fail';
        $this->pathToLog = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "iipqa_tests.log";
    }

    public function testRunSuccessfulQA()
    {
        $single = new \iipqa\contentmodels\Single($this->inputDirectoryPass, $this->pathToLog, null);
        $this->assertContains(true, $single->applyQaTests());
    }

    public function testCheckExtensions()
    {
        $single = new \iipqa\contentmodels\Single($this->inputDirectoryFail, $this->pathToLog, null);
        $single->applyQaTests();
        $this->assertContains("5 => 'png'", file_get_contents($this->pathToLog));
    }

    public function testCheckFilePairs()
    {
        $single = new \iipqa\contentmodels\Single($this->inputDirectoryFail, $this->pathToLog, null);
        $single->applyQaTests();
        $this->assertContains("2 => 2", file_get_contents($this->pathToLog));
        $this->assertContains("3 => 4", file_get_contents($this->pathToLog));
    }

    public function testCheckForDirectories()
    {
        $single = new \iipqa\contentmodels\Single($this->inputDirectoryFail, $this->pathToLog, null);
        $single->applyQaTests();
        $this->assertContains("tests/fixtures/single/fail/foo", file_get_contents($this->pathToLog));
    }
    
    protected function tearDown()
    {
        @unlink($this->pathToLog);
    }
}

<?php

namespace iipqa\utils;

/**
 * The Reader is instantiated in the instances of ContentModelQaFramework.
 */
class Reader
{
    /**
     *
     */
    public function read($path, $dirs_only = false)
    {
        $path = rtrim($path, DIRECTORY_SEPARATOR);
        $pattern = $path . DIRECTORY_SEPARATOR . "*";
        if ($dirs_only) {
            $file_list = glob($pattern, GLOB_ONLYDIR);
        } else {
            $file_list = glob($pattern);
        }

        return $file_list;
    }

    public function readRecursive($path)
    {
        $file_list = array();
        $directory_iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
        foreach ($directory_iterator as $filepath => $info) {
            $file_list[] = $filepath;
        }
        return $file_list;
    }
}

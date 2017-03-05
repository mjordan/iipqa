#!/usr/bin/env php
<?php

/**
 * Sample post-iipqa script. Is executed after all iipqa tests are run.
 * Must have executable permissions and contain a shebang (that is, the script
 * should be executable on its own).
 *
 * Exit with non-0 if you want iipqa to also exit with non-0 (e.g., if you're
 * running iipqa within a script).
 */

// Do something simple to illustrate how the post-iipqa file works.
$path_to_output_file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'iipqa_post_example.txt';
if (!file_put_contents($path_to_output_file, "Foo")) {
    exit(1);
}

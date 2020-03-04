<?php

//確認資料夾是否有東西
function is_dir_empty(string $dir) : bool
{
    $handle = opendir($dir);

    while (false !== ($entry = readdir($handle))) {

        if ($entry != "." && $entry != "..") {

            closedir($handle);

            return FALSE;

        }

    }

    closedir($handle);

    return TRUE;
}

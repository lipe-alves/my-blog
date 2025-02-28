<?php

function hide_base_path(string $fullpath, string $basepath): string
{
    $basepath = str_replace("\\", "/", $basepath);
    $fullpath = str_replace("\\", "/", $fullpath);
    $protected_path = str_replace($basepath, "", $fullpath);
    $protected_path = preg_replace("/\/+/", "/", $protected_path);
    return $protected_path;
}
<?php

function get_file_extension(string $path): string 
{
    preg_match("/\.\w+$/", $path, $matches);
    $ext = $matches[0];
    $ext = str_replace(".", "", $ext);
    return $ext;
}

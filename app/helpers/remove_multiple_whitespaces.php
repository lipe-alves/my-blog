<?php

function remove_multiple_whitespaces(string $str): string
{
    $str = preg_replace("/\s+/", " ", $str);
    return trim($str);
}

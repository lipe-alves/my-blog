<?php

namespace App\Helpers;

/** @return bool */
function str_contains(string $str, string $search)
{
    return strpos($str, $search) !== false;
}

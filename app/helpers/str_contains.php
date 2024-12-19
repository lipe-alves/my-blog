<?php

if (!function_exists("str_contains")) {
    /** @return bool */
    function str_contains(string $str, string $search)
    {
        return strpos($str, $search) !== false;
    }
}

<?php

/** @return bool */
function ends_with(string $string, string $end_string)
{
    return substr($string, -strlen($end_string)) === $end_string;
}

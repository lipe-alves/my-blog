<?php

/** @return bool */
function starts_with(string $string, string $start_string)
{
    return strncmp($string, $start_string, strlen($start_string)) === 0;
}

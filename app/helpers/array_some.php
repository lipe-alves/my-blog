<?php

function array_some(array $array, callable $callback): bool {
    $filtered = array_filter($array, $callback);
    return count($filtered) > 0;
}

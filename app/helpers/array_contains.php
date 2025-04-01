<?php

function array_contains(
    array $arr, 
    mixed $target,
    callable $test = null
): bool {
    if (!isset($test)) {
        $test = function (mixed $item) use ($target) {
            return $item === $target;
        };
    }

    $item = array_find($arr, $test);
    return $item !== null;
}

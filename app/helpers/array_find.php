<?php

function array_find(array $arr, callable $filter): mixed {
    $item = array_filter($arr, $filter);
    return count($item) === 0 ? null : $item[0];
}

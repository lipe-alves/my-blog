<?php

define("ARRAY_FIND_NORMALIZE_INDEXES", 1);

function array_find(array $arr, callable $filter, int $mode = ARRAY_FIND_NORMALIZE_INDEXES): mixed {
    $item = array_filter($arr, $filter);
    if ($mode === ARRAY_FIND_NORMALIZE_INDEXES) {
        $item = array_values($item); // Normalize indexes
    }
    return count($item) === 0 ? null : $item[0];
}

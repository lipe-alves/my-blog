<?php 

function get_initials(string $text): array {
    $words = explode(" ", $text);
    $initials = array_map(function ($word) {
        return $word[0];
    }, $words);
    return $initials;
}
<?php

function remove_newlines(string $text, string $replacement = ""): string 
{
    $text = preg_replace("/(\r\n|\n)/", $replacement, $text);
    return $text;
}

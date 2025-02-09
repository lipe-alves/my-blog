<?php 

function remove_accents(string $str): string 
{
    $replacements = [
        "à" => "a",
        "á" => "a",
        "â" => "a",
        "ã" => "a",
        "ä" => "a",
        "ç" => "c",
        "è" => "e",
        "é" => "e",
        "ê" => "e",
        "ë" => "e",
        "ì" => "i",
        "í" => "i",
        "î" => "i",
        "ï" => "i",
        "ñ" => "n",
        "ò" => "o",
        "ó" => "o",
        "ô" => "o",
        "õ" => "o",
        "ö" => "o",
        "ù" => "u",
        "ú" => "u",
        "û" => "u",
        "ü" => "u",
        "ý" => "y",
        "ÿ" => "y",
    ];

    foreach ($replacements as $accented => $non_accented) {
        $replacements[mb_strtoupper($accented, "UTF-8")] = strtoupper($non_accented);
    }

    $accented = array_keys($replacements);
    $non_accented = array_values($replacements);

    return str_replace(
        $accented,
        $non_accented,
        $str
    );
}

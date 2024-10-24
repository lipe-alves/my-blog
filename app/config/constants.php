<?php

$script_name = dirname($_SERVER["SCRIPT_NAME"]);
$base_uri = str_replace("/index.php", "", $script_name);

define("APP_NAME", "Pensamentos de Escrivaninha");
define("ROOT_PATH", dirname(__DIR__, 2));
define("BASE_URI", $base_uri);
define("OPERATORS",  [
    "comparison" => ["=", "!=", "<>", ">", "<", ">=", "<="],
    "logical" => ["AND", "OR", "NOT"],
    "arithmetic" => ["+", "-", "*", "/"],
    "bitwise" => ["&", "|", "^", "~", "<<", ">>"],
    "assignment" => ["=", "+=", "-=", "*=", "/=", "%="],
    "others" => ["LIKE", "IN", "BETWEEN", "IS NULL", "IS NOT NULL"]
]);
define("DEFAULT_PROFILE_PICTURE", "public/images/profile/perfil-padrao.jpeg");

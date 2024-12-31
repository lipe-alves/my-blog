<?php

$script_name = dirname($_SERVER["SCRIPT_NAME"]);
$base_uri = str_replace("/index.php", "", $script_name);
$server_name = $_SERVER["SERVER_NAME"];
$is_https = !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off";
$api_url = ($is_https ? "https": "http")."://$server_name$base_uri/api";

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
define("API_URL", $api_url);
define("DEFAULT_DATE_FORMAT", "d/m/Y, H:i:s");

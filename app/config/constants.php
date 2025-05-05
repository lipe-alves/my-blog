<?php

$script_name = dirname($_SERVER["SCRIPT_NAME"]);
$base_uri = str_replace("/index.php", "", $script_name);
$server_name = $_SERVER["SERVER_NAME"];
$server_port = $_SERVER["SERVER_PORT"];
if ($server_port) {
    $server_name .= ":$server_port";
}
$is_https = !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off";
$website_url = ($is_https ? "https" : "http") . "://$server_name$base_uri";
$api_url = "$website_url/api";

define("VERSION", "2025-05-05 15:20:00");
define("ROOT_PATH", dirname(__DIR__, 2));
define("BASE_URI", $base_uri);
define("DEFAULT_PROFILE_PICTURE", "/public/images/profile/perfil-padrao.jpeg");
define("WEBSITE_URL", $website_url);
define("API_URL", $api_url);
define("DEFAULT_DISPLAY_DATETIME_FORMAT", "%e de %B de %Y às %H:%M");
define("DEFAULT_DATABASE_DATETIME_FORMAT", "Y-m-d H:i:s");
define("SESSION_TIMEOUT", 1800);
define("UPLOAD_PATH", ROOT_PATH . "/public/media");
define("UPLOAD_URI", BASE_URI . "/public/media");

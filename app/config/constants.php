<?php

$script_name = dirname($_SERVER["SCRIPT_NAME"]);
$base_uri = str_replace("/index.php", "", $script_name);
$server_name = $_SERVER["SERVER_NAME"];
$is_https = !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off";
$api_url = ($is_https ? "https": "http")."://$server_name$base_uri/api";

define("ROOT_PATH", dirname(__DIR__, 2));
define("BASE_URI", $base_uri);
define("DEFAULT_PROFILE_PICTURE", "public/images/profile/perfil-padrao.jpeg");
define("API_URL", $api_url);
define("DEFAULT_DISPLAY_DATETIME_FORMAT", "d/m/Y, H:i:s");
define("DEFAULT_DATABASE_DATETIME_FORMAT", "Y-m-d H:i:s");
define("SESSION_TIMEOUT", 1800);

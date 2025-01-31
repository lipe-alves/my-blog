<?php

require_once "./vendor/autoload.php";
require_once "./create_default_env_file.php";

use Dotenv\Dotenv;

if (!file_exists("./.env")) {
    create_default_env_file();
}

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    setlocale(LC_ALL, "pt_BR", "pt_BR.utf-8", "pt_BR.utf-8", "portuguese");
    date_default_timezone_set("America/Sao_Paulo");

    require_once "./app/config/constants.php";
    require_once "./app/helpers/index.php";
    require_once "./app/config/routes.php";
} catch (\Exception $exception) {
    header("Content-Type: application/json");

    $message = $exception->getMessage();
    $line = $exception->getLine();
    $file = $exception->getFile();
    $message = "$message\nCode line: $line\nFile: $file";

    echo json_encode([
        "success" => false,
        "message" => $message,
        "data"    => []
    ]);
}



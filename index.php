<?php

require_once "./vendor/autoload.php";

use Dotenv\Dotenv;

if (!file_exists("./.env")) {
    create_default_env_file();
}

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    setlocale(LC_ALL, "pt_BR", "pt_BR.utf-8", "pt_BR.utf-8", "portuguese");
    date_default_timezone_set("America/Sao_Paulo");

    start_database();
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

function create_default_env_file()
{
    file_put_contents(".env", "LC_ALL=pt_BR.UTF-8
DB_PORT=3306
DB_HOST=127.0.0.1
DB_NAME=my_blog
DB_USER=root
DB_PASSWORD=");
}

function start_database()
{
    $sql = file_get_contents("./app/config/start-database.sql");
    $conn = new PDO(
        "mysql:host=$_ENV[DB_HOST];port=$_ENV[DB_PORT];dbname=$_ENV[DB_NAME]",
        $_ENV["DB_USER"],
        $_ENV["DB_PASSWORD"]
    );
    $conn->exec($sql);
}

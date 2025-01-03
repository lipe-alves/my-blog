<?php

require_once "./vendor/autoload.php";

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    function start_database()
    {
        $sql = file_get_contents("./app/config/start-database.sql");
        $sql = preg_replace("/[\n\r]+/", "", $sql);
        $conn = new PDO(
            "mysql:host=$_ENV[DB_HOST];port=$_ENV[DB_PORT];dbname=$_ENV[DB_NAME]",
            $_ENV["DB_USER"],
            $_ENV["DB_PASSWORD"]
        );
        $conn->exec($sql);
    }

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

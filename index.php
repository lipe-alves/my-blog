<?php

require_once "./vendor/autoload.php";

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

require_once "./app/config/constants.php";
require_once "./app/helpers/index.php";
require_once "./app/config/routes.php";

function start_database()
{
    $sql = file_get_contents("./app/config/start-database.sql");
    $conn = new PDO(
        "mysql:host=$_ENV[DB_HOST]",
        $_ENV["DB_USER"],
        $_ENV["DB_PASSWORD"]
    );
    $conn->exec($sql);
}

start_database();

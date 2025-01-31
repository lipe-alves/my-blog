<?php
require_once "./vendor/autoload.php";
require_once "./create_default_env_file.php";

use Dotenv\Dotenv;

if (!file_exists("./.env")) {
    create_default_env_file();
}

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

start_database();

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

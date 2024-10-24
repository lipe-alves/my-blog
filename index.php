<?php

require_once "./vendor/autoload.php";
require_once "./app/config/constants.php";
require_once "./app/helpers/index.php";
require_once "./app/config/routes.php";

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

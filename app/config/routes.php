<?php

namespace App\Config\Routes;

require_once "./app/routers/home_router.php";
require_once "./app/routers/about_me_router.php";
require_once "./app/routers/post_router.php";
require_once "./app/routers/comment_router.php";
require_once "./app/routers/category_router.php";
require_once "./app/routers/media_router.php";
require_once "./app/routers/settings_router.php";
require_once "./app/routers/admin_router.php";
require_once "./app/routers/tests_router.php";

use App\Core\Router;

use function App\Core\Routers\home_router;
use function App\Core\Routers\about_me_router;
use function App\Core\Routers\post_router;
use function App\Core\Routers\comment_router;
use function App\Core\Routers\category_router;
use function App\Core\Routers\media_router;
use function App\Core\Routers\settings_router;
use function App\Core\Routers\admin_router;
use function App\Core\Routers\tests_router;

$router = new Router();

$router->addMiddleware("*", "\\App\\Middlewares\\SessionMiddleware::execute");
$router->addMiddleware("*", "\\App\\Middlewares\\AdminMiddleware::execute");

home_router($router);
about_me_router($router);
post_router($router);
comment_router($router);
category_router($router);
media_router($router);
settings_router($router);
admin_router($router);
tests_router($router);

$router->get("/404", "\\App\\Controllers\\NotFoundController::index");

$router->setHandlers([
    "page_not_found" => "\\App\\Controllers\\NotFoundController::index",
    "error"          => "\\App\\Middlewares\\ErrorMiddleware::execute"
]);

$router->listen();

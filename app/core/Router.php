<?php

namespace App\Core;

use App\Core\Request;
use App\Core\Response;
use App\Exceptions\ApiException;
use App\Exceptions\ResourceNotFoundException;

class Router
{
    private const PATH_PARAM_PATTERN = "[a-zA-Z\d\-_]+"; 

    private array $routes = [];
    private array $handlers;
    private array $middlewares;

    public function addMiddleware(string $path_template, string $middleware)
    {
        $this->middlewares[] = [
            "path_template" => $path_template,
            "path_pattern"  => $this->convertPathTemplateToRegex($path_template),
            "middleware"    => $middleware
        ];
    }

    public function addRoute(string $method, string $path_template, string $controller, array $middlewares = [])
    {
        if (!starts_with($path_template, "/")) {
            $path_template = "/" . $path_template;
        }

        if (!ends_with($path_template, "/")) {
            $path_template = $path_template . "/";
        }

        foreach ($middlewares as $middleware) {
            $this->addMiddleware($path_template, $middleware);
        }

        $this->routes[] = [
            "method"        => strtoupper($method),
            "path_template" => $path_template,
            "path_pattern"  => $this->convertPathTemplateToRegex($path_template),
            "controller"    => $controller
        ];
    }

    public function setHandlers(array $handlers)
    {
        extract($handlers);

        $this->handlers = [
            "route_not_found" => isset($route_not_found) ? $route_not_found : null,
            "page_not_found"  => isset($page_not_found)  ? $page_not_found  : null,
            "error"           => isset($error)           ? $error           : null,
            "global"          => isset($global)  ? $global  : null,
        ];
    }

    public function dispatch(Request $request, Response $response)
    {
        $actual_path = $request->getPath();
        $method = $request->getMethod();

        extract($this->handlers);

        try {
            if (isset($error)) {
                set_error_handler(function ($level, $message, $file, $line) {
                    throw new ApiException("Erro [$level]: $message em $file na linha $line", 500);
                });
            }

            foreach ($this->routes as $route) {
                $route_method = $route["method"];
                $path_pattern = $route["path_pattern"];
                $path_template = $route["path_template"];
                $controller = $route["controller"];

                $route_matched = $route_method === strtoupper($method) && (bool)preg_match($path_pattern, $actual_path);
                $params = $this->extractParams($path_template, $actual_path);

                foreach ($params as $key => $value) {
                    $request->setParams($key, $value);
                }

                if ($route_matched) {
                    $this->executeMiddlewares($actual_path, $request, $response);
                    $this->executeRouteHandler($controller, $request, $response);
                    return;
                }
            }

            $is_api = str_contains($actual_path, "api");

            if ($is_api) {
                if (isset($route_not_found)) {
                    $this->executeMiddlewares($actual_path, $request, $response);
                    $this->executeRouteHandler($route_not_found, $request, $response);
                    return;
                }
            } else {
                if (isset($page_not_found)) {
                    $this->executeMiddlewares($actual_path, $request, $response);
                    $this->executeRouteHandler($page_not_found, $request, $response);
                    return;
                }
            }

            throw new ResourceNotFoundException("Rota não encontrada");
        } catch (\Exception $exception) {
            if (isset($error)) {
                $this->executeRouteHandler($error, $request, $response, $exception);
            } else {
                throw $exception;
            }
        }
    }

    /**
     * Registra uma rota GET
     *
     * @param path O caminho da rota
     * @param controller A função que será executada ao chamar a rota
     */
    public function get(string $path, string $controller, array $middlewares = [])
    {
        $this->addRoute("GET", $path, $controller, $middlewares);
    }

    /**
     * Registra uma rota POST
     *
     * @param path O caminho da rota
     * @param controller A função que será executada ao chamar a rota
     */
    public function post(string $path, string $controller, array $middlewares = [])
    {
        $this->addRoute("POST", $path, $controller, $middlewares);
    }

    /**
     * Registra uma rota DELETE
     *
     * @param path O caminho da rota
     * @param controller A função que será executada ao chamar a rota
     */
    public function delete(string $path, string $controller, array $middlewares = [])
    {
        $this->addRoute("DELETE", $path, $controller, $middlewares);
    }

    /**
     * Registra uma rota PATCH
     *
     * @param path O caminho da rota
     * @param controller A função que será executada ao chamar a rota
     */
    public function patch(string $path, string $controller, array $middlewares = [])
    {
        $this->addRoute("PATCH", $path, $controller, $middlewares);
    }

    /**
     * Executa um "Escutador" de rotas
     */
    public function listen()
    {
        session_start();
        mb_internal_encoding("UTF-8");

        $raw_data = file_get_contents("php://input");
        $path = $this->getRequestPath($_SERVER["REQUEST_URI"]);
        $method = strtoupper($_SERVER["REQUEST_METHOD"]);
        $params = [
            "path"  => $path,
        ];

        $_PATCH = [];
        $_PUT = [];
        $_DELETE = [];

        if ($method === "PATCH" || $method === "PUT" || $method === "POST" || $method === "DELETE") {
            try {
                $parsed_data = json_decode($raw_data, true);

                if (is_array($parsed_data)) {
                    if ($method === "PATCH") {
                        $_PATCH = $parsed_data;
                    } else if ($method === "PUT") {
                        $_PUT = $parsed_data;
                    } else if ($method === "POST") {
                        $_POST = $parsed_data;
                    } else if ($method === "DELETE") {
                        $_DELETE = $parsed_data;
                    }
                }
            } catch (\Exception $err) {
            }
        }

        $GLOBALS["_PATCH"] = $_PATCH;
        $GLOBALS["_PUT"] = $_PUT;
        $GLOBALS["_DELETE"] = $_DELETE;

        $params["patch"] = $_PATCH;
        $params["put"] = $_PUT;
        $params["post"] = $_POST;
        $params["delete"] = $_DELETE;

        $response = new Response();
        $request = new Request($params);

        $this->dispatch($request, $response);
    }

    protected function convertPathTemplateToRegex(string $path_template)
    {
        $pattern = "#^" . preg_replace("/:\w+/", "(".self::PATH_PARAM_PATTERN.")", $path_template) . "$#";
        $pattern = preg_replace("/\*/", ".*", $pattern);
        $pattern = preg_replace("/\/$/", "/*", $pattern);
        return $pattern;
    }

    protected function getRequestPath(string $request_uri)
    {
        $base = BASE_URI;
        $uri = $request_uri;
        $parsed_uri = parse_url($uri);
        $path = $parsed_uri["path"];

        if (!empty($base) && strpos($path, $base) === 0) {
            $path = substr($path, strlen($base));
        }

        if (!starts_with($path, "/")) {
            $path = "/" . $path;
        }

        if (!ends_with($path, "/")) {
            $path = $path . "/";
        }

        return $path;
    }

    protected function extractParams(string $path_template, string $actual_path)
    {
        $pattern = $this->convertPathTemplateToRegex($path_template);

        if (preg_match($pattern, $actual_path, $matches)) {
            array_shift($matches);

            preg_match_all("/:(".self::PATH_PARAM_PATTERN.")/", $path_template, $param_names);
            $param_names = $param_names[1];

            $params = array_combine($param_names, $matches);

            return $params;
        }

        return [];
    }

    protected function executeRouteHandler(
        string $controller,
        Request $request,
        Response $response,
        \Exception $exception = null
    ) 
    {
        execute_class_method($controller, [$request, $response, $exception]);
    }

    protected function executeMiddlewares(
        string $actual_path,
        Request $request,
        Response $response
    ) 
    {
        $middlewares = [];

        foreach ($this->middlewares as $middleware) {
            $middleware_matched = (bool)preg_match($middleware["path_pattern"], $actual_path);
            if ($middleware_matched) {
                $middlewares[] = $middleware["middleware"];
            }
        }

        if (isset($this->handlers["global"])) {
            $this->executeRouteHandler($this->handlers["global"], $request, $response);
        }

        foreach ($middlewares as $middleware) {
            $this->executeRouteHandler($middleware, $request, $response);
        }
    }
}

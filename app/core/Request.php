<?php

namespace App\Core;

class Request
{
    private $params;
    private $get;
    private $post;
    private $patch;
    private $put;
    private $files;
    private $method;
    private $requestUri;
    private $path;
    private $headers;
    private $session;
    private $load;

    public function __construct(array $params)
    {
        $path_params = isset($params["path_params"]) ? $params["path_params"] : [];
        $get = isset($params["get"]) ? $params["get"] : $_GET;
        $post = isset($params["post"]) ? $params["post"] : $_POST;
        $patch = isset($params["patch"]) ? $params["patch"] : $GLOBALS["_PATCH"];
        $put = isset($params["put"]) ? $params["put"] : $GLOBALS["_PUT"];
        $delete = isset($params["delete"]) ? $params["delete"] : $GLOBALS["_DELETE"];

        $files = isset($params["files"]) ? $params["files"] : $_FILES;
        $method = isset($params["method"]) ? $params["method"] : $_SERVER["REQUEST_METHOD"];
        $request_uri = isset($params["request_uri"]) ? $params["request_uri"] : $_SERVER["REQUEST_URI"];
        $path = isset($params["path"]) ? $params["path"] : $request_uri;
        $headers = isset($params["headers"]) ? $params["headers"] : getallheaders();
        $session = isset($params["session"]) ? $params["session"] : $_SESSION;
        $load = isset($params["load"]) ? $params["load"] : [];

        $this->params = $path_params;
        $this->get = $get;
        $this->post = $post;
        $this->patch = $patch;
        $this->put = $put;
        $this->delete = $delete;
        $this->files = $files;
        $this->method = $method;
        $this->requestUri = $request_uri;
        $this->path = $path;
        $this->headers = $headers;
        $this->session = $session;
        $this->load = $load;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function setParams(string $key, mixed $value)
    {
        $this->params[$key] = $value;
    }

    public function getGet()
    {
        return $this->get;
    }

    public function setGet(string $key, mixed $value)
    {
        $this->get[$key] = $value;
    }

    public function getPost()
    {
        return $this->post;
    }

    public function getPatch()
    {
        return $this->patch;
    }

    public function getPut()
    {
        return $this->put;
    }

    public function getDelete()
    {
        return $this->delete;
    }

    public function getFiles()
    {
        return $this->files;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getRequestUri()
    {
        return $this->requestUri;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getHeader()
    {
        return $this->headers;
    }

    public function getSession()
    {
        $this->session = $_SESSION;
        return $this->session;
    }

    public function clearSession()
    {
        session_unset();
    }

    public function destroySession()
    {
        session_destroy();
    }

    public function regenareSessionId($delete_old_session = false)
    {
        session_regenerate_id($delete_old_session);
    }

    public function startSession(array $options = [])
    {
        session_start($options);
    }

    public function setSession(string $key, mixed $value)
    {
        $_SESSION[$key] = $value;
        $this->session = $_SESSION;
    }

    public function reloadSession() {
        $_SESSION["reload"] = true;
        $this->session = $_SESSION;
    }

    public function getLoad()
    {
        return $this->load;
    }

    public function setLoad(string $key, mixed $value)
    {
        $this->load[$key] = $value;
    }
}

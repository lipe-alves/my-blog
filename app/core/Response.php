<?php

namespace App\Core;

class Response
{
    /** @property integer */
    private $status;
    /** @property array */
    private $headers;
    /** @property mixed */
    private $body;

    public function __construct()
    {
        $this->status = 200;
        $this->headers = [];
        $this->body = "";
    }

    public function setStatus(int $status)
    {
        $this->status = $status;
        http_response_code($status);
        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setHeader(string $key, string $value)
    {
        $this->headers[$key] = $value;
        header("$key: $value");
        return $this;
    }

    public function setJson(array $data)
    {
        $this->setHeader("Content-Type", "application/json");
        $this->body = json_encode(
            $data instanceof EntityModel ? $data->data : $data
        );
        return $this;
    }

    public function setBody(mixed $body)
    {
        $this->body = $body;
        return $this;
    }

    public function setCookie(
        string $key,
        string $value,
        int $expire = 0,
        string $path = "",
        string $domain = "",
        bool $secure = false,
        bool $http_only = false
    ) {
        setcookie(
            $key,
            $value,
            $expire,
            $path,
            $domain,
            $secure,
            $http_only
        );
        return $this;
    }

    public function redirect($url)
    {
        $this->setStatus(302);
        $this->setHeader("Location", $url);
        $this->send();
        exit();
    }

    public function send()
    {
        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }

        echo $this->body;
    }
}

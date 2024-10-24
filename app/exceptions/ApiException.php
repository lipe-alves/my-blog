<?php

namespace App\Exceptions;

class ApiException extends \Exception
{
    /** @property array */
    protected $data;

    public function __construct(string $message, int $status, array $data = [])
    {
        parent::__construct($message, $status);
        $this->data = $data;
    }

    public function getStatus()
    {
        return $this->code;
    }

    public function getData()
    {
        return $this->data;
    }
}

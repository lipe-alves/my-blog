<?php

namespace App\Exceptions;


class ResourceNotFoundException extends ApiException
{
    public function __construct(string $message, array $data = [])
    {
        parent::__construct($message, 404, $data);
    }
}

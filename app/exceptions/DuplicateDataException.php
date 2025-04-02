<?php

namespace App\Exceptions;

class DuplicateDataException extends ApiException
{
    public function __construct(string $message, array $data = [])
    {
        parent::__construct($message, 400, $data);
    }
}

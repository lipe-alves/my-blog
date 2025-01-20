<?php

namespace App\Exceptions;

class UnauthorizedException extends ApiException
{
    public function __construct(
        string $message = "You're not authorized to access this resource",
        array $data = []
    ) {
        parent::__construct($message, 401, $data);
    }
}

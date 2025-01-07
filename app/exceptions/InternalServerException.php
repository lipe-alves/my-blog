<?php

namespace App\Exceptions;

class InternalServerException extends ApiException
{
    public function __construct(array $data = [])
    {
        parent::__construct("Erro interno do servidor", 500, $data);
    }
}

<?php

namespace App\Exceptions;

class MissingParamException extends ApiException
{
    public function __construct(string $parameter, array $data = [])
    {
        parent::__construct("O campo $parameter é obrigatório.", 400, $data);
    }
}

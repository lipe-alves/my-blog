<?php

namespace App\Exceptions;

class InvalidParamException extends ApiException
{
    public function __construct(
        string $parameter,
        array $valid_types,
        array $data = []
    ) {
        $valid_types = implode(", ", $valid_types);
        parent::__construct("Formato inválido para o campo $parameter. Esse campo deve ser dos seguintes tipos: $valid_types.", 400, $data);
    }
}

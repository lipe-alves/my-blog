<?php

namespace App\Exceptions;

class InvalidFormatException extends ApiException
{
    public function __construct(
        string $parameter,
        array $valid_formats,
        array $data = []
    ) {
        $valid_formats = implode(", ", $valid_formats);
        parent::__construct("Formato inválido para o campo $parameter. Esse campo deve ser dos seguintes formatos: $valid_formats.", 400, $data);
    }
}

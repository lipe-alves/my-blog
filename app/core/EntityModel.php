<?php

namespace App\Core;

use App\Exceptions\InvalidInputException;
use App\Functions;

class EntityModel
{
    /** @property array */
    protected $data;
    /** @property array */
    protected $skeleton;

    protected function __construct(array $input = [])
    {
        $this->skeleton = [
            "id" => [
                "type"     => "integer",
                "required" => true,
                "default"  => null
            ]
        ];

        $this->fill($input);
    }

    public function __get(string $key)
    {
        if ($key === "data") {
            return $this->data;
        }

        if ($key === "skeleton") {
            return $this->skeleton;
        }

        if (array_key_exists($key, $this->skeleton)) {
            return $this->data[$key];
        }

        $trace = debug_backtrace();
        trigger_error(
            "Propriedade indefinida via __get(): " . $key .
                " em " . $trace[0]["file"] .
                " na linha " . $trace[0]["line"],
            E_USER_NOTICE
        );

        return null;
    }

    public function __set(string $key, $value)
    {
        if (array_key_exists($key, $this->data)) {
            $this->data[$key] = $value;
        }

        $trace = debug_backtrace();
        trigger_error(
            "Propriedade indefinida via __set(): " . $key .
                " em " . $trace[0]["file"] .
                " na linha " . $trace[0]["line"],
            E_USER_NOTICE
        );
    }

    public function __serialize()
    {
        return $this->data;
    }

    public function columns()
    {
        return array_keys($this->skeleton);
    }

    public function validate(array $columns_to_ignore = [])
    {
        foreach ($this->skeleton as $column => $column_config) {
            $ignore = count($columns_to_ignore) > 0 && in_array($column, $columns_to_ignore);

            if ($ignore) {
                continue;
            }

            extract($column_config);

            $column_filled = (
                array_key_exists($column, $this->data) &&
                isset($this->data[$column])
            );

            if ($column_filled && $type === "string") {
                $column_filled = $column_filled && strlen($this->data[$column]) > 0;
            }

            $correct_type = (
                $column_filled && (
                    $type === "enum" && isset($values)
                    ? in_array($this->data[$column], $values)
                    : gettype($this->data[$column]) === $type
                )
            );

            if ($required && !$column_filled) {
                throw new InvalidInputException("O campo \"$column\" é obrigatório", $this->data);
            }

            if ($column_filled && !$correct_type) {
                throw new InvalidInputException(
                    $type !== "enum"
                        ? "\"$column\" aceita somente valores do tipo $type"
                        : "Valor inválido do campo \"$column\". Valores válidos: " . implode(", ", $values),
                    $this->data
                );
            }

            if ($column_filled && $type === "string") {
                $str_length = strlen($this->data[$column]);

                if ($str_length < $min_size || $str_length > $max_size) {
                    throw new InvalidInputException(
                        "O campo \"$column\" aceita valores com no máximo $max_size e no mínimo $min_size caracteres",
                        $this->data
                    );
                }
            }
        }
    }

    public function fill(array $input)
    {
        foreach ($this->skeleton as $column => $column_config) {
            extract($column_config);

            $value_is_filled = array_key_exists($column, $input) && isset($input[$column]);
            $has_default_value = isset($default);

            if (!$value_is_filled) {
                if ($has_default_value) {
                    $this->data[$column] = $default;
                }
            } else {
                $this->data[$column] = $input[$column];
            }
        }
    }

    public function cast(array $input)
    {

        foreach ($this->skeleton as $column => $column_config) {
            $ignore = !array_key_exists($column, $input);

            if ($ignore) {
                continue;
            }

            extract($column_config);

            if ($type === "string") {
                $input[$column] = (string)$input[$column];
            } else if ($type === "integer") {
                $input[$column] = (int)$input[$column];
            }
        }

        $this->fill($input);
    }

    /** @return EntityModel */
    public static function create(array $data)
    {
        return new EntityModel($data);
    }
}

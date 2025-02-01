<?php

namespace App\Services;

use App\Core\DatabaseService;
use App\Exceptions\InvalidFormatException;
use App\Exceptions\MissingParamException;
use App\Exceptions\InvalidInputException;

class ReaderService extends DatabaseService
{
    public function getReaders(array $columns, array $data)
    {
        $data["table"] = "Reader";
        $readers = $this->select($columns, $data);
        return $readers;
    }

    public function getReader(array $columns, array $data): array|null
    {
        $readers = $this->getReaders($columns, $data);
        return count($readers) === 0 ? null : $readers[0];
    }

    public function createReader(array $data): array|false
    {
        extract($data);

        if (!isset($email)) {
            throw new MissingParamException('"email"');
        }

        if (!validate_email($email)) {
            throw new InvalidFormatException('"email"', ["exemplo@exemplo.com"]);
        }

        if (isset($fullname)) {
            $name_parts = explode(" ", $fullname);
            $first_name = $name_parts[0];
            array_shift($name_parts);
            $last_name = implode(" ", $name_parts);
        } 

        $first_name = remove_multiple_whitespaces($first_name);
        $last_name = remove_multiple_whitespaces($last_name);

        if (!isset($first_name) || !$first_name) {
            throw new MissingParamException('"primeiro nome"');
        }

        if (!isset($last_name) || !$last_name) {
            throw new MissingParamException('"último nome"');
        }

        if (!isset($photo)) {
            $photo = null;
        }

        $last_id = $this->insert("Reader", [
            [
                "email"      => $email,
                "first_name" => $first_name,
                "last_name"  => $last_name,
                "photo"      => $photo,
            ]
        ]);

        $success = $last_id !== false;
        if (!$success) return false;

        $last_inserted_reader = $this->getReader(["r.*"], ["r.id" => $last_id]);

        return $last_inserted_reader;
    }

    public function updateReader(string $id, array $updates): array|false
    {
        if (array_key_exists("fullname", $updates)) {
            $name_parts = explode(" ", $updates["fullname"]);
            $first_name = $name_parts[0];
            $last_name = count($name_parts) > 1 ? $name_parts[1] : ""; 

            $first_name = remove_multiple_whitespaces($first_name);
            $last_name = remove_multiple_whitespaces($last_name);

            $updates["first_name"] = $first_name;
            $updates["last_name"] = $last_name;
            unset($updates["fullname"]);
        }

        extract($updates);

        if (isset($email) && !validate_email($email)) {
            throw new InvalidFormatException('"email"', ["exemplo@exemplo.com"]);
        }
        
        if (isset($first_name) && !$first_name) {
            throw new MissingParamException('"primeiro nome"');
        }

        if (isset($last_name) && !$last_name) {
            throw new MissingParamException('"último nome"');
        }

        $success = $this->update("Reader", $updates, ["r.id" => $id]);
        if (!$success) return false;

        $updated_data = $this->getReader(["r.*"], ["r.id" => $id]);
        return $updated_data;
    }
}

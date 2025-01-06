<?php

namespace App\Services;

use App\Core\DatabaseConnection;

class SettingsService
{
    public static function get(string $id): string
    {
        $conn = DatabaseConnection::create();
        $config = $conn->selectOne("SELECT value FROM Settings WHERE id = :id", ["id" => $id]);
        return $config["value"];
    }

    public static function getAll(): array
    {
        $conn = DatabaseConnection::create();
        $configs = $conn->selectAll("SELECT * FROM Settings");
        $settings = [];

        foreach ($configs as $config) {
            $settings[$config["id"]] = $config["value"];
        }

        return $settings;
    }

    public static function set(string $id, string $value): bool
    {
        $conn = DatabaseConnection::create();
        $success = $conn->update("UPDATE Settings SET value = :value WHERE id = :id", [
            "id"    => $id,
            "value" => $value
        ]);
        return $success;
    }

    public static function __callStatic(string $name, array $arguments)
    {
        if (str_contains($name, "get")) {
            $id = str_replace("get", "", $name);
            $id = strtolower($id);
            return self::select($id);
        } else if (str_contains($name, "set")) {
            $id = str_replace("set", "", $name);
            $id = strtolower($id);
            return self::set($id, ...$arguments);
        }
    }
}

<?php

namespace App\Services;

use App\Models\SettingsModel;

class AdminService
{
    public static function authenticate(string $password): bool
    {
        $adm_password = SettingsModel::get("adm_password");
        $passwords_match = AuthService::verifyPassword($password, $adm_password);
        $_SESSION["admin"] = $passwords_match;
        return $passwords_match;
    }
}

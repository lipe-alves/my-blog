<?php

namespace App\Services;

use App\Core\DatabaseConnection;

class SettingsService
{
    public static function getBlogName() {
        $conn = DatabaseConnection::create();
        $blog_name = $conn->select("SELECT valor FROM Settings WHERE id = 'blog_name'");
        $blog_name = $blog_name["value"];
        return $blog_name;
    }
}

<?php

namespace App\Services;

use App\Exceptions\MissingParamException;

class MediaLibraryService
{
    public static function updateFile(string $path, array $updates) 
    {
        $path = UPLOAD_PATH."/$path";
        $path = str_replace("//", "/", $path);

        extract($updates);

        if (!isset($filename) || !$filename) {
            throw new MissingParamException("nome do arquivo");
        }

        $file_info = extract_data_from_path($path);
        $content = file_get_contents($path);
        $new_path =  str_replace($file_info["name"], $filename, $path);

        file_put_contents($new_path, $content);
        unlink($path);

        return extract_data_from_path($new_path);
    }

    public static function deleteFile(string $path): bool
    {
        $path = UPLOAD_PATH."/$path";
        $path = str_replace("//", "/", $path);
        $success = unlink($path);
        return $success;
    }
}

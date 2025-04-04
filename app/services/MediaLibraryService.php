<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Exceptions\MissingParamException;
use App\Exceptions\ResourceNotFoundException;
USE App\Exceptions\DuplicateDataException;

class MediaLibraryService
{
    private static function treatPath(string $path): string 
    {
        $path = UPLOAD_PATH."/$path";
        $path = str_replace("//", "/", $path);
        return $path;
    }

    public static function updateFolder(string $path, array $updates) 
    {
        $path = MediaLibraryService::treatPath($path);

        extract($updates);

        if (!isset($name) || !$name) {
            throw new MissingParamException("nome da pasta");
        }
        
        $old_path = $path;
        $dir_data = extract_data_from_path($old_path, DEFAULT_DISPLAY_DATETIME_FORMAT);
        $new_path = str_replace($dir_data["name"], $name, $old_path);

        if (!file_exists($old_path)) {
            throw new ResourceNotFoundException("$old_path não existe!");
        }

        if (file_exists($new_path) && $dir_data["name"] !== $name) {
            throw new DuplicateDataException("Nome da pasta (\"$name\") já está sendo utilizado!");
        }

        if (!rename($old_path, $new_path)) {
            throw new ApiException("Erro ao renomear pasta.", 500);
        }

        return extract_data_from_path($new_path, DEFAULT_DISPLAY_DATETIME_FORMAT);
    }

    public static function deleteFolder(string $path): bool
    {
        $success = false;
        return $success;
    }

    public static function updateFile(string $path, array $updates) 
    {
        $path = MediaLibraryService::treatPath($path);

        extract($updates);

        if (!isset($name) || !$name) {
            throw new MissingParamException("nome do arquivo");
        }

        $file_info = extract_data_from_path($path, DEFAULT_DISPLAY_DATETIME_FORMAT);
        $content = file_get_contents($path);
        $new_path =  str_replace($file_info["name"], $name, $path);

        file_put_contents($new_path, $content);
        unlink($path);

        return extract_data_from_path($new_path, DEFAULT_DISPLAY_DATETIME_FORMAT);
    }

    public static function deleteFile(string $path): bool
    {
        $path = MediaLibraryService::treatPath($path);
        $success = unlink($path);
        return $success;
    }
}

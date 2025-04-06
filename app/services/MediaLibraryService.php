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

    public static function updateMedia(string $path, array $updates): array 
    {
        $path = MediaLibraryService::treatPath($path);
        $old_path = $path;

        if (!file_exists($old_path)) {
            throw new ResourceNotFoundException("$old_path não existe!");
        }

        $media_data = extract_data_from_path($old_path, DEFAULT_DISPLAY_DATETIME_FORMAT);
        $is_directory = $media_data["type"] === "directory";
        $field = $is_directory ? "da pasta" : "do arquivo";

        extract($updates);

        if (!isset($name) || !$name) {
            throw new MissingParamException("nome $field");
        }
        
        $new_path = str_replace($media_data["name"], $name, $old_path);

        if (file_exists($new_path) && $media_data["name"] !== $name) {
            throw new DuplicateDataException("Nome $field (\"$name\") já está sendo utilizado!");
        }

        if (!rename($old_path, $new_path)) {
            throw new ApiException("Erro ao renomear.", 500);
        }

        $new_media = extract_data_from_path($new_path, DEFAULT_DISPLAY_DATETIME_FORMAT);
        return $new_media;
    }

    private static function deleteFolder(string $path): bool
    {
        $map = map_directory($path);
        $dir = $map[0];

        foreach ($dir["children"] as $child) {
            $success = true;

            if ($child["type"] === "directory") {
                $success = MediaLibraryService::deleteFolder($child["path"]);
            } else {
                $success = unlink($child["path"]);
            }

            if (!$success)
                return false;
        }

        return rmdir($path);
    }

    public static function deleteMedia(string $path): bool
    {
        $path = MediaLibraryService::treatPath($path);
        $media_data = extract_data_from_path($path);
        $is_directory = $media_data["type"] === "directory";
        $success = false;

        if ($is_directory) {
            $success = MediaLibraryService::deleteFolder($path);
        } else {
            $success = unlink($path);
        }

        return $success;
    }
}

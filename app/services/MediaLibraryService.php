<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Exceptions\InternalServerException;
use App\Exceptions\MissingParamException;
use App\Exceptions\ResourceNotFoundException;
use App\Exceptions\DuplicateDataException;
use App\Exceptions\InvalidInputException;
use App\Exceptions\InvalidFormatException;

class MediaLibraryService
{
    private const PROHIBITED_CHARS = [
        "\\", 
        "/",
        ":",
        "*",
        "?",
        '"', 
        "<",
        ">",
        "|"
    ];

    private static function treatPath(string $path): string 
    {
        $path = hide_base_path($path, UPLOAD_PATH);
        $path = UPLOAD_PATH."/$path";
        $path = preg_replace("/\/+/", "/", $path);
        return $path;
    }

    private static function treatFolderName(string $name): string 
    {
        $name = str_replace(MediaLibraryService::PROHIBITED_CHARS, "", $name);
        $name = remove_multiple_whitespaces($name);
        $name = remove_newlines($name);
        $name = substr($name, 0, 254);
        return $name;
    }

    private static function uploadFile(string $path, array $file): array 
    {
        $tmp_name = $file["tmp_name"];
        $content = file_get_contents($tmp_name);
        $file_path = "$path/$file[name]";
        $file_path = MediaLibraryService::treatPath($file_path);
        
        file_put_contents($file_path, $content);

        return extract_data_from_path($file_path, DEFAULT_DISPLAY_DATETIME_FORMAT);
    }

    public static function createMedia(
        string $path, 
        string $type, 
        array $params
    ): array 
    {
        $path = MediaLibraryService::treatPath($path);

        if (!file_exists($path)) {
            throw new ResourceNotFoundException("$path não existe!");
        }

        $dir_data = extract_data_from_path($path);
        if ($dir_data["type"] !== "directory") {
            throw new InvalidInputException("$path não é um caminho para uma pasta!");
        }

        if (!in_array($type, ["file", "folder"])) {
            throw new InvalidFormatException('"tipo"', ["file", "folder"]);
        }

        extract($params);

        if ($type === "folder") {
            if (!isset($name) || !$name) {
                throw new MissingParamException("nome $field");
            }
    
            if (!is_string($name)) {
                throw new InvalidFormatException("nome $field", ["texto"]);
            }

            $name = MediaLibraryService::treatFolderName($name);

            $folder_path = "$path/$name";
            $folder_path = MediaLibraryService::treatPath($folder_path);
            if (file_exists($folder_path)) {
                throw new InvalidInputException("Nome de pasta (\"$name\") já existe!");
            }
            
            $success = mkdir($folder_path); 
            if (!$success) {
                throw new InternalServerException();
            }

            $dir_data = extract_data_from_path($folder_path, DEFAULT_DISPLAY_DATETIME_FORMAT);

            return $dir_data;
        } else {
            if (!isset($files) || !$files) {
                throw new MissingParamException("lista de arquivos");
            }

            if (!is_array($files)) {
                throw new InvalidFormatException("lista de arquivos", ["vetor"]);
            }

            $results = [];

            foreach ($files as $file) {
                $file_data = MediaLibraryService::uploadFile($path, $file);
                $results[] = $file_data;
            }

            return $results;
        }
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

        if (!is_string($name)) {
            throw new InvalidFormatException("nome $field", ["texto"]);
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

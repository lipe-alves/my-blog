<?php 

function map_directory(string $directory, string $date_format = DEFAULT_DATABASE_DATETIME_FORMAT): array 
{
    $result = [];
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        $dir_path = $item->isDir() ? $item->getPathname() : $item->getPath();
        $dir_path = str_replace(ROOT_PATH, "", $dir_path);

        $info = [
            "path" => $item->getPathname(),
            "name" => $item->getFilename(),
            "type" => $item->isDir() ? "directory" : "file",
            "size" => $item->getSize(),
            "permissions" => substr(sprintf("%o", $item->getPerms()), -4),
            "owner" => $item->getOwner(),
            "group" => $item->getGroup(),
            "modification_time" => strftime($date_format, $item->getMTime()),
            "access_time" => strftime($date_format, $item->getATime()),
            "creation_time" => strftime($date_format, $item->getCTime()),
        ];
        
        $info["path"] = str_replace(ROOT_PATH, "", $info["path"]);

        if ($item->isDir()) {
            if (!array_key_exists("files", $info)) {
                $info["files"] = [];
            }

            $result[$dir_path] = $info;
        } else {
            $file_path = $info["path"];
            
            $mime_type = mime_content_type($item->getRealPath());
            $info["mime_type"] = $mime_type;
            
            $result[$dir_path]["files"][$file_path] = $info;
        }
    }

    return $result;
}

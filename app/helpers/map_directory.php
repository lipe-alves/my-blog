<?php 

function map_directory(string $directory): array 
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
            "modification_time" => date("Y-m-d H:i:s", $item->getMTime()),
            "access_time" => date("Y-m-d H:i:s", $item->getATime()),
            "creation_time" => date("Y-m-d H:i:s", $item->getCTime()),
        ];
        
        $info["path"] = str_replace(ROOT_PATH, "", $info["path"]);

        if ($item->isDir()) {
            if (!array_key_exists("files", $info)) {
                $info["files"] = [];
            }

            $result[$dir_path] = $info;
        } else {
            $file_path = $info["path"];
            $result[$dir_path]["files"][$file_path] = $info;
        }
    }

    return $result;
}

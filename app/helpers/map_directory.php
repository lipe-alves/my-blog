<?php 

function map_directory(string $directory, string $date_format = DEFAULT_DATABASE_DATETIME_FORMAT): array 
{
    $result = [];
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    $items = [];

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
        $info["path"] = str_replace("\/", "/", $info["path"]);
        $info["path"] = str_replace("\\", "/", $info["path"]);

        if ($info["type"] === "directory") {
            $info["children"] = [];
        }

        $items[$info["path"]] = $info;

        $parent_path = explode("/", $info["path"]);
        array_pop($parent_path);
        $parent_path = implode("/", $parent_path);

        if (array_key_exists($parent_path, $items)) {
            $items[$parent_path]["children"][$info["path"]] = $info;
        }
    }

    foreach ($items as $path => $item) {

    }

    return $result;
}

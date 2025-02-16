<?php 

function extract_data_from_path(string $path, string $date_format) {
    if (!str_contains($path, ROOT_PATH)) {
        $path = ROOT_PATH.$path;
    }

    $info = new SplFileInfo($path);

    $data = [
        "path" => $info->getPathname(),
        "name" => $info->getFilename(),
        "type" => $info->isDir() ? "directory" : "file",
        "size" => $info->getSize(),
        "permissions" => substr(sprintf("%o", $info->getPerms()), -4),
        "owner" => $info->getOwner(),
        "group" => $info->getGroup(),
        "modification_time" => trim(strftime($date_format, $info->getMTime())),
        "access_time" => trim(strftime($date_format, $info->getATime())),
        "creation_time" => trim(strftime($date_format, $info->getCTime())),
    ];
    
    $data["path"] = str_replace(ROOT_PATH, "", $data["path"]);
    $data["path"] = str_replace("\/", "/", $data["path"]);
    $data["path"] = str_replace("\\", "/", $data["path"]);

    if ($data["type"] === "directory") {
        $data["children"] = [];
    }

    return $data;
}

function map_directory(string $directory, string $date_format = DEFAULT_DATABASE_DATETIME_FORMAT): array 
{
    $map = [];

    $map[$directory] = extract_data_from_path($directory, $date_format);

    $children = scandir($directory);

    foreach ($children as $child_path) {
        if ($child_path === "." || $child_path === "..") {
            continue;
        }

        $info = extract_data_from_path("$directory/$child_path", $date_format);

        if ($info["type"] === "directory") {
            $path = ROOT_PATH.$info["path"];
            $children_map = map_directory($path, $date_format);
            $info["children"] = $children_map[$path]["children"];
        }

        $map[$directory]["children"][$info["path"]] = $info;
    }

    file_put_contents("map.txt", print_r($map, true));

    return $map;
}

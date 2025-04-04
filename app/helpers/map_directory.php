<?php 

function extract_data_from_path(
    string $path, 
    string $date_format = "%Y-%m-%d"
) {
    $info = new SplFileInfo($path);

    $data = [
        "path"              => $info->getPathname(),
        "name"              => $info->getFilename(),
        "type"              => $info->isDir() ? "directory" : "file",
        "size"              => $info->getSize(),
        "permissions"       => $info->getPerms(),
        "owner"             => $info->getOwner(),
        "group"             => $info->getGroup(),
        "modification_time" => trim(strftime($date_format, $info->getMTime())),
        "access_time"       => trim(strftime($date_format, $info->getATime())),
        "creation_time"     => trim(strftime($date_format, $info->getCTime())),
    ];
    
    $data["path"] = str_replace("\/", "/", $data["path"]);
    $data["path"] = str_replace("\\", "/", $data["path"]);
    $data["path"] = preg_replace("/\/+/", "/", $data["path"]);
    
    if ($data["type"] === "directory") {
        $data["children"] = [];
    } else {
        $data["mimetype"] = mime_content_type($path);
        $data["extension"] = get_file_extension($data["path"]);
    }

    // foreach ($data as $key => $value) {
    //     if (is_string($value)) {
    //         $data[$key] = utf8_encode($value);
    //     }
    // }

    return $data;
}

function map_directory(
    string $directory,
    string $date_format = DEFAULT_DATABASE_DATETIME_FORMAT
): array 
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
            $children_map = map_directory($info["path"], $date_format);
            $info["children"] = $children_map[0]["children"];
        }

        $map[$directory]["children"][$info["path"]] = $info;
    }
    
    $map[$directory]["children"] = array_values($map[$directory]["children"]);
    $map = array_values($map);

    return $map;
}

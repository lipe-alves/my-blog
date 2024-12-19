<?php

function execute_class_method(string $class, array $params)
{
    $class_parts = explode("::", $class);
    $class_name = $class_parts[0];
    $class_method = $class_parts[1];

    $class_instance = new $class_name();

    call_user_func_array([$class_instance, $class_method], $params);
}

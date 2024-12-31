<?php

function date_is_valid(string $date, string $format = "Y-m-d") {
    return date($format, strtotime($date)) == $date;
}

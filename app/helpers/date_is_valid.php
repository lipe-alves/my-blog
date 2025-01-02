<?php

function date_is_valid(string $date, string $format = DEFAULT_DATABASE_DATETIME_FORMAT) {
    return date($format, strtotime($date)) === $date;
}

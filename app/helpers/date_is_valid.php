<?php

function date_is_valid(string $date, string $format = DEFAULT_DATE_FORMAT) {
    return date($format, strtotime($date)) == $date;
}

<?php

function validate_email(string $email): bool
{
    $email_pattern = "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/";
    return (bool)preg_match($email_pattern, $email);
}

<?php

class PasswordCheckerService
{
    public const MIN_LENGTH_PASSWORD = 8;

    public static function isAValidPassword(string $plainPassword): bool
    {
        if (strlen($plainPassword) < self::MIN_LENGTH_PASSWORD) {
            return false;
        }
        if (!preg_match('/[A-Z]/', $plainPassword)
            || !preg_match('/\d/', $plainPassword)
            || !preg_match('/[^A-Za-z0-9]/', $plainPassword)) {
            return false;
        }

        return true;
    }
}
<?php
namespace RgpdCompliance\Service;

use RgpdCompliance\RgpdCompliance;

class PasswordCheckerService
{
    public function isAValidPassword(string $plainPassword): bool
    {
        $minLengthPassword = RgpdCompliance::getConfigValue(RgpdCompliance::CONFIG_PASSWORD_LENGTH);
        $hasUpper = RgpdCompliance::getConfigValue(RgpdCompliance::CONFIG_PASSWORD_HAS_UPPER);
        $hasNumber = RgpdCompliance::getConfigValue(RgpdCompliance::CONFIG_PASSWORD_HAS_NUMBER);
        $hasSpecialChars = RgpdCompliance::getConfigValue(RgpdCompliance::CONFIG_PASSWORD_HAS_SPECIAL_CHARS);

        if (strlen($plainPassword) < $minLengthPassword) {
            return false;
        }

        if (($hasUpper && !preg_match('/[A-Z]/', $plainPassword) )
            || ($hasNumber && !preg_match('/\d/', $plainPassword))
            || ($hasSpecialChars && !preg_match('/[^A-Za-z0-9]/', $plainPassword))) {
            return false;
        }
        return true;
    }
}
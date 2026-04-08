<?php

namespace App\Support;

class UserHelper
{
    public static function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    public static function normalizeMiddleInitial(?string $middleInitial): ?string
    {
        if ($middleInitial === null) {
            return null;
        }

        $middleInitial = trim($middleInitial);

        if ($middleInitial === '') {
            return null;
        }

        return strtoupper(substr($middleInitial, 0, 1));
    }
}   
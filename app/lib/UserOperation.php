<?php

namespace app\lib;

class UserOperation
{
    const RoleGuest = 'guest';
    const RoleAdmin = 'admin';
    const RoleUser = 'user';

    public static function getRoleUser() {
        $result = self::RoleGuest;
        if (isset($_SESSION['user']['id']) && $_SESSION['user']['is_admin']) {
            $result = self::RoleAdmin;
        } elseif (isset($_SESSION['user']['id'])) {
            $result = self::RoleUser;
        }

        return $result;
    }

    public static function validatePassword($password) {
        if (strlen($password) <= 8) {
            $_SESSION['error'] = "Ваш пароль должен быть длиннее 8 символов";
            return false;
        }
        $hasUpper = false;
        $hasLower = false;
        $hasNumber = false;
        $hasSpecial = false;
        for ($i = 0; $i < strlen($password); $i++) {
            $char = $password[$i];

            if (ctype_upper($char)) $hasUpper = true;
            if (ctype_lower($char)) $hasLower = true;
            if (ctype_digit($char)) $hasNumber = true;
            if (ctype_punct($char)) $hasSpecial = true;
        }
        if (!$hasUpper && !$hasLower && !$hasNumber && !$hasSpecial) {
            $_SESSION['error'] =
                "Ваш пароль должен содержать букву в верхнем регистре, нижнем регистре, цифру и спецсимвол";
            return false;
        }
        return true;
    }
}
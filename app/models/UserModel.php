<?php

namespace app\models;

use app\core\BaseModel;
use app\lib\UserOperation;
use app\lib\Encrypted;

class UserModel extends BaseModel
{
    public function registration($data)
    {
        $login = !empty($data['login']) ? trim($data['login']) : null;
        $password = !empty($data['password']) ? trim($data['password']) : null;
        $rPassword = !empty($data['rPassword']) ? trim($data['rPassword']) : null;

        $isValidPassword = UserOperation::validatePassword($password);
        if (!$isValidPassword) {
            return false;
        }
        if ($rPassword != $password) {
            $_SESSION['error'] = "Пароль и Повторённый пароль должны совпадать";
        }

        $masterKey = Encrypted::createMasterKey();
        $password = password_hash($password, PASSWORD_DEFAULT);

        return $this->insert(
            "INSERT INTO users (login, password, master_key) VALUES (:login, :password, :master_key)",
            [
                "login" => $login,
                "password" => $password,
                "master_key" => $masterKey
            ]
        );
    }

    public function authByLogin($login, $password) {
        $result = false;
        $error_message = '';

        if (empty($login)) {
            $error_message .= "Введите ваш логин<br>";
        }
        if (empty($password)) {
            $error_message .= "Введите ваш пароль<br>";
        }

        if (empty($error_message)) {
            $user = $this->select(
                "SELECT * FROM users WHERE login = :login",
                ["login" => $login]
            );

            if (!empty($user[0])) {
                if (password_verify($password, $user[0]['password'])) {
                    $_SESSION['user']['id'] = $user[0]['id'];
                    $_SESSION['user']['login'] = $user[0]['login'];
                    $_SESSION['user']['is_admin'] = $user[0]['is_admin'];

                    $result = true;
                } else {
                    $error_message .= "Неверный пароль<br>";
                }
            } else {
                $error_message .= "Пользователь не найден<br>";
            }
        }

        return [
            'result' => $result,
            'error_message' => $error_message
        ];
    }
}
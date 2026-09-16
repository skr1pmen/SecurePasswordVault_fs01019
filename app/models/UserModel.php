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

        $userId = $this->insert(
            "INSERT INTO users (login, password, master_key) VALUES (:login, :password, :master_key)",
            [
                "login" => $login,
                "password" => $password,
                "master_key" => $masterKey
            ]
        );
        if (!empty($userId)) {
            $this->addLog(
                "Пользователь с id {$userId} зарегистрировался",
                "info",
                $_SERVER['REMOTE_ADDR'],
                $userId
            );
        }
        return $userId;
    }

    public function login($data) {
        $login = !empty($data['login']) ? trim($data['login']) : null;
        $password = !empty($data['password']) ? trim($data['password']) : null;

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

                    if (!empty($user[0]['id'])) {
                        $this->addLog(
                            "Пользователь с id {$user[0]['id']} авторизировался",
                            "info",
                            $_SERVER['REMOTE_ADDR'],
                            $user[0]['id']
                        );
                    }

                    return $user[0]['id'];
                } else {
                    $error_message .= "Неверный пароль<br>";
                }
            } else {
                $error_message .= "Пользователь не найден<br>";
            }
        }

        $_SESSION['error'] = $error_message;
        return null;
    }

    public function getDataUser($id) {
        $user = $this->select(
            "SELECT login, created_at FROM users WHERE id = :id",
            ['id' => $id]
        );
        if (!empty($user[0])) {
            return $user[0];
        } else {
            $this->addLog(
                "Не удалось найти данные пользователя по id {$id}",
                "error",
                $_SERVER['REMOTE_ADDR'],
                $_SESSION['user']['id']
            );
            return false;
        }
    }
    public function getLogsByUser($id) {
        $logs = $this->select(
            "SELECT * FROM logs 
         WHERE user_id = :id ORDER BY id DESC LIMIT 50",
            ['id' => $id]
        );
        return $logs[0];
    }
}
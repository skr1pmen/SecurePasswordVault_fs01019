<?php

namespace app\models;

use app\core\BaseModel;
use app\lib\Encrypted;

class MainModel extends BaseModel
{
    public function new($data) {
        $name = trim($data['name']);
        $password = trim($data['password']);
        $login = !empty($data['login']) ? $data['login'] : null;
        $url = !empty($data['url']) ? $data['url'] : null;
        $category = !empty($data['category']) ? $data['category'] : null;
        $desc = !empty($data['desc']) ? $data['desc'] : null;

        $masterKey = $this->select(
            "SELECT master_key FROM users WHERE id = :id",
            [
                "id" => $_SESSION['user']['id']
            ]
        );
        $crypto_pass = Encrypted::encrypted($masterKey[0]['master_key'], $password);

        $id = $this->insert(
            'INSERT INTO passwords (user_id, title, username, password, url, category, notes)
                    VALUES (:user_id, :title, :name, :password, :url, :category, :desc)',
            [
                "user_id" => $_SESSION['user']['id'],
                "title" => $name,
                "name" => $login,
                "password" => $crypto_pass,
                "url" => $url,
                "category" => $category,
                "desc" => $desc
            ]
        );
        if (!empty($id)) {
            $this->addLog(
                "Пользователь создал новую запись с id: {$id}",
                "info",
                $_SERVER['REMOTE_ADDR'],
                $_SESSION['user']['id']
            );
            return true;
        }
        return false;
    }
}
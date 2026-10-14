<?php

namespace app\models;

use app\core\BaseModel;
use app\lib\Encrypted;

class MainModel extends BaseModel
{
    public function new($data)
    {
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

    public function getCountPasswordsByUser($user_id)
    {
        $count = $this->select(
            "SELECT COUNT(id) FROM passwords WHERE user_id = :user_id",
            ['user_id' => $user_id]
        );
        if (!empty($count[0]['count'])) {
            return $count[0]['count'];
        }
        return 0;
    }

    public function getPasswordsByUser($user_id)
    {
        $passwords = $this->select(
            "SELECT id, title FROM passwords 
                 WHERE user_id = :user_id AND deleted_at IS NULL
                 ORDER BY updated_at DESC LIMIT 5",
            ['user_id' => $user_id]
        );

        if (!empty($passwords)) {
            return $passwords;
        }
        return [];
    }

    public function getDataByPassword($id, $isShow)
    {
        $data = $this->select(
            "SELECT id, title, username, password, 
            url, category, notes, created_at FROM passwords
            WHERE id = :id",
            ["id" => $id]
        );

        if (!empty($data)) {
            $data[0]['password'] = stream_get_contents($data[0]['password']);

            if (!empty($isShow)) {
                $masterKey = $this->select(
                    "SELECT master_key FROM users WHERE id = :id",
                    ['id' => $_SESSION['user']['id']]
                );

                $data[0]['password'] = Encrypted::decrypted($masterKey[0]['master_key'], $data[0]['password']);
            }

            return $data[0];
        }
        return false;
    }

    public function editPassword($id, $data)
    {
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

        if (empty($crypto_pass)) return false;
        $result = $this->update(
            "UPDATE passwords 
                SET title = :title,
                    username = :username,
                    password = :password,
                    url = :url,
                    category = :category,
                    notes = :notes 
                WHERE id = :id",
            [
                'title' => $name,
                'username' => $login,
                'password' => $crypto_pass,
                'url' => $url,
                'category' => $category,
                'notes' => $desc,
                "id" => $id
            ]
        );
        if (!empty($result)) return true;
        return false;
    }

    public function deletePassword($id) {
        return $this->update(
            "UPDATE passwords SET deleted_at = NOW() WHERE id = :id",
            ["id" => $id]
        );
    }

    public function getBasketData($user_id) {
        $passwords = $this->select(
            "SELECT id, title FROM passwords 
                 WHERE user_id = :user_id AND deleted_at IS NOT NULL
                 ORDER BY deleted_at DESC",
            ['user_id' => $user_id]
        );

        if (!empty($passwords)) {
            return $passwords;
        }
        return [];
    }

    public function recoveryPassword($id) {
        $result = $this->update(
            "UPDATE passwords SET deleted_at = NULL WHERE id = :id",
            ["id" => $id]
        );
        if (empty($result)) {
            $_SESSION['error'] = "Ошибка восстановления";
        }
    }

    public function destructionPassword($id) {
        $result = $this->delete(
            "DELETE FROM passwords WHERE id = :id",
            ["id" => $id]
        );
        if (empty($result)) {
            $_SESSION['error'] = "Ошибка удаления ";
        }
    }

    public function generatePassword($length, $useUppercase, $useDigits, $useSymbol) {
        $lowercase = 'qwertyuiopasdfghjklzxcvbnm';
        $uppercase = 'QWERTYUIOPASDFGHJKLZXCVBNM';
        $digits = '1234567890';
        $symbols = "!@#$%^&*()_-+={}[]?/";

        $all = [];
        $all[] = $lowercase;
        if ($useUppercase) $all[] = $uppercase;
        if ($useDigits) $all[] = $digits;
        if ($useSymbol) $all[] = $symbols;

        $passwords = [];
        for ($i = 0; $i < 10; $i++) {
            $password = "";
            foreach ($all as $category) {
                $password .= $category[random_int(0, strlen($category) - 1)];
            }
            $all_ = implode("", $all);
            for ($j = 0; $password < ($length - strlen($password)); $j++) {
                $password .= $all_[random_int(0, strlen($all_) - 1)];
            }
            $passwords[] = str_shuffle($password);
        }
        return $passwords;
    }

    public function exportPassword($type, $password) {
        $user = $this->select(
            "SELECT password FROM users WHERE id = :id",
            ['id' => $_SESSION['user']['id']]
        );
        if (empty($user)) {
            $_SESSION['error'] = "Произошла неизвестная ошибка!";
            return false;
        }
        if (!password_verify($password, $user[0]['password'])) {
            $_SESSION['error'] = "Неверный пароль!";
            return false;
        }

        $data = $this->select(
            "SELECT title, username, password, url, category, notes, created_at, updated_at 
                    FROM passwords 
                    WHERE user_id = :id AND deleted_at IS NULL",
            ["id" => $_SESSION['user']['id']]
        );
        if ($type === "json") {
            // Формат JSON
//            $fp = fopen('passwords.json', 'w');
//            fwrite($fp, "[\n");
//            $total = count($data);
//            foreach ($data as $i => $item) {
//                fwrite($fp, json_encode($item, JSON_UNESCAPED_UNICODE));
//                if ($i < $total - 1) {
//                    fwrite($fp, ",\n");
//                } else {
//                    fwrite($fp, "\n");
//                }
//            }
//            fwrite($fp, "]");
//            fclose($fp);
//
//            header('Content-Type: application/json');
//            header('Content-Disposition: attachment; filename="passwords.json"');
//            readfile('passwords.json');
//            exit;


//            $dataJson = '';
//            foreach ($data as $item) {
//                $dataJson .= json_encode($item);
//            }
//            var_dump($dataJson);
        } else {
            // Формат CSV

        }
    }
}
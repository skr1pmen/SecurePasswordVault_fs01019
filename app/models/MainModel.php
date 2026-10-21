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

    public function deletePassword($id)
    {
        return $this->update(
            "UPDATE passwords SET deleted_at = NOW() WHERE id = :id",
            ["id" => $id]
        );
    }

    public function getBasketData($user_id)
    {
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

    public function recoveryPassword($id)
    {
        $result = $this->update(
            "UPDATE passwords SET deleted_at = NULL WHERE id = :id",
            ["id" => $id]
        );
        if (empty($result)) {
            $_SESSION['error'] = "Ошибка восстановления";
        }
    }

    public function destructionPassword($id)
    {
        $result = $this->delete(
            "DELETE FROM passwords WHERE id = :id",
            ["id" => $id]
        );
        if (empty($result)) {
            $_SESSION['error'] = "Ошибка удаления ";
        }
    }

    public function generatePassword($length, $useUppercase, $useDigits, $useSymbol)
    {
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

    public function exportPassword($type, $password)
    {
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

        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['password'] = stream_get_contents($data[$i]['password']);
        }

        if ($type === "json") {
            // Формат JSON
            header("Content-Type: application/json");
            header("Content-Disposition: attachment; filename=\"passwords.json\"");

            $dataJson = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

            file_put_contents("php://output", $dataJson);
            exit;
        } else {
            // Формат CSV
            header("Content-Type: text/csv; charset=utf-8");
            header("Content-Disposition: attachment; filename=\"passwords.csv\"");

            $out = fopen("php://output", 'w');
            fwrite($out, "\xEF\xBB\xBF");

            if (!empty($data)) {
                fputcsv($out, array_keys($data[0]), ';');
            }
            foreach ($data as $item) {
                fputcsv($out, $item, ';');
            }
            fclose($out);
            exit;
        }
    }

    public function parseJson($content)
    {
        return json_decode($content, true);
    }

    public function parseCsv($content)
    {
        function detectDelimiter($line)
        {
            $semicolon = substr_count($line, ";");
            $comma = substr_count($line, ",");
            $tab = substr_count($line, "\t");

            if ($tab > $semicolon && $tab > $comma) {
                return "\t";
            }
            return $semicolon >= $comma ? ";" : ",";
        }

        $stream = fopen("php://temp", "r+");
        fwrite($stream, $content);
        rewind($stream);

        $firstLine = fgets($stream);
        $delimiter = detectDelimiter($firstLine);
        rewind($stream);

        $header = fgetcsv($stream, 0, $delimiter);
        if (empty($header)) {
            fclose($stream);
            return [];
        }

        $header[0] = preg_replace("/^\xEF\xBB\xBF/", '', $header[0]);

        $rows = [];
        while (($cells = fgetcsv($stream, 0, $delimiter)) !== false) {
            if ($cells === [null]) continue;
            $cells = array_pad($cells, count($header), null);
            $rows[] = array_combine($header, array_slice($cells, 0, count($header)));
        }

        fclose($stream);
        return $rows;
    }

    public function importPasswords($passwords, $user_id)
    {
        foreach ($passwords as $password) {
            $result = $this->select(
                "SELECT id FROM passwords WHERE user_id = :user_id AND title = :title AND password = :password",
                [
                    "user_id" => $user_id,
                    "title" => $password['title'],
                    "password" => $password['password']
                ]
            );

            if (!empty($result)) continue;

            $this->insert(
                "INSERT INTO passwords (user_id, title, password, url, category, notes, username) 
                        VALUES (:user_id, :title, :password, :url, :category, :notes, :username)",
                [
                    "user_id" => $user_id,
                    "title" => $password['title'],
                    "password" => $password['password'],
                    "url" => !empty($password['url']) ? $password['url'] : null,
                    "category" => !empty($password['category']) ? $password['category'] : null,
                    "notes" => !empty($password['notes']) ? $password['notes'] : null,
                    "username" => !empty($password['username']) ? $password['username'] : null
                ]
            );
        }
    }
}
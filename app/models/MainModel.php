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

        var_dump($crypto_pass);
        var_dump(Encrypted::decrypted($masterKey[0]['master_key'], $crypto_pass));
    }
}
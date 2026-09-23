<?php

namespace app\models;

use app\core\BaseModel;

class MainModel extends BaseModel
{
    public function new($data) {
        $name = trim($data['name']);
        $password = trim($data['password']);
        $login = !empty($data['login']) ? $data['login'] : null;
        $url = !empty($data['url']) ? $data['url'] : null;
        $category = !empty($data['category']) ? $data['category'] : null;
        $desc = !empty($data['desc']) ? $data['desc'] : null;


    }
}
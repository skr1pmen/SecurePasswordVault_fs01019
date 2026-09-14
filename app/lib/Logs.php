<?php

namespace app\lib;

use app\core\BaseModel;

class Logs extends BaseModel
{
    public static function addLog($log, $type, $ip = null) {
        $this->insert(
            "INSERT INTO logs (type, ip_address, details) VALUES (:type, :ip, :log)",
            [
                "type" => $type,
                "ip" => $ip,
                "log" => $log
            ]
        );
    }
}
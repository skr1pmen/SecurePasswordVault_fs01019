<?php

namespace app\lib;

class Encrypted
{
    public static function createMasterKey() {
        $key = random_bytes(32);

        return self::toBase64($key);
    }

    public static function toBase64($key) {
        return base64_encode($key);
    }

    public static function fromBase64($key) {
        return base64_decode($key);
    }
}
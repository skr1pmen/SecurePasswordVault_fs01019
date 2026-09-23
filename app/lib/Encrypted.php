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

    public static function encrypted($masterKey, $data) {
        $nonce = random_bytes(16);
        $tag = "";
        $result = openssl_encrypt(
            $data,
            'AES-256-CBC',
            self::fromBase64($masterKey),
            OPENSSL_RAW_DATA,
            $nonce,
            $tag,
            '',
            16
        );
        if ($result == false) {
            $_SESSION['error'] = "Ошибка шифрования";
            return false;
        }
        return $nonce.$tag.$result;
    }

    public static function decrypted($masterKey, $data) {
        $nonce = substr($data, 0, 16);
        $tag = substr($data, 16, 16);
        $password = substr($data, 32);

        $result = openssl_decrypt(
          $password,
          'AES-256-CBC',
          self::fromBase64($masterKey),
          OPENSSL_RAW_DATA,
          $nonce,
          $tag
        );
        if ($result == false) {
            $_SESSION['error'] = 'Ошибка расшифровки';
            return false;
        }
        return $result;
    }
}
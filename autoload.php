<?php
spl_autoload_register(function ($class) {
    $class = str_replace(
        "\\",
        DIRECTORY_SEPARATOR,
        $class
    );
    $path = $class . ".php";
    if (is_readable($path)) {
        require $path;
    }
});
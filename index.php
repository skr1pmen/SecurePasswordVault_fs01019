<?php
session_start();
ini_set("display_errors", 1);
error_reporting(E_ALL);


use app\core\Router;
require "autoload.php";

$router = new Router();
$router->run();
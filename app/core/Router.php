<?php

namespace app\core;

use app\lib\UserOperation;

class Router
{
    protected $params = [];

    public function match() {
        $url = trim($_SERVER['REQUEST_URI'], '/');
        if (!empty($url)) {
            if (strpos($url, '?') !== false) {
                $link = explode('?', $url);
                if (!empty($link[0])) {
                    $url = $link[0];
                }
            }
            $params = explode('/', $url);
            if (!empty($params[0]) && !empty($params[1])) {
                $this->params = [
                    'controller' => $params[0],
                    'action' => $params[1]
                ];
            } else {
                return false;
            }
        } else {
            $params = require 'app/config/params.php';
            $this->params = [
                'controller' => $params['defaultController'],
                'action' => $params['defaultAction']
            ];
        }
        return true;
    }

    public function run() {
        if ($this->match()) {
            $path_controller = 'app\\controllers\\' . ucfirst($this->params['controller']) . 'Controller';
            if (class_exists($path_controller)) {
                $action = 'action' . ucfirst($this->params['action']);
                if (method_exists($path_controller, $action)) {
                    $controller = new $path_controller($this->params);
                    $behaviors = $controller->behaviors();
                    if ($this->checkBehaviors($behaviors)) {
                        $controller->$action();
                    }
                } else {
                    echo "Action не найден: " . $action;
                }
            } else {
                echo "Класс не найден: " . $path_controller;
            }
        } else {
            echo "Не найдено!";
        }
    }

    private function checkBehaviors($behaviors) {
        if (empty($behaviors['access']['rules'])) {
            return true;
        }
        foreach ($behaviors['access']['rules'] as $role) {
            if (in_array($this->params['action'], $role['actions'])) {
                if (in_array(UserOperation::getRoleUser(), $role['roles'])) {
                    return true;
                } else {
                    if (isset($role['matchCallback'])) {
                        call_user_func($role['matchCallback']);
                    } else {
                        echo "403";
                    }
                    return false;
                }
            }
        }
        return true;
    }
}
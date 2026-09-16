<?php

namespace app\controllers;

use app\core\InitController;
use app\models\UserModel;

class UserController extends InitController
{
    public function actionRegistration()
    {
        $this->view->title = "Страница регистрации";

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $userModel = new UserModel();
            $userId = $userModel->registration($_POST);
            if (!empty($userId)) {
                $_SESSION['user']['id'] = $userId;
                $this->redirect('/');
            }
        }

        $this->render('registration');
    }
    public function actionLogin() {
        $this->view->title = "Страница авторизации";

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $userModel = new UserModel();
            $userId = $userModel->login($_POST);
            if (!empty($userId)) {
                $_SESSION['user']['id'] = $userId;
                $this->redirect('/');
            }
        }
        $this->render('login');
    }
    public function actionProfile() {
        $this->view->title = "Страница профиля";

        $userModel = new UserModel();
        $userData = $userModel->getDataUser($_SESSION['user']['id']);
        $userLogs = $userModel->getLogsByUser($_SESSION['user']['id']);

        $this->render(
            'profile',
            [
                'userData' => $userData,
                'userLogs' => $userLogs
            ]
        );
    }
}
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
                $this->redirect('/');
            }
        }

        $this->render('registration');
    }
}
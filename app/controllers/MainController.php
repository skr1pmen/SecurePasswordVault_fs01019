<?php

namespace app\controllers;

use app\core\InitController;
use app\lib\UserOperation;
use app\models\MainModel;

class MainController extends InitController
{
    public function behaviors() {
        return [
            'access' => [
                'rules' => [
                    [
                        'actions' => ['index', 'new'],
                        'roles' => [UserOperation::RoleUser,],
                        'matchCallback' => function () {
                            $this->redirect('/user/login');
                        }
                    ],
                ]
            ]
        ];
    }

    public function actionIndex() {
        $this->view->title = "Главная страница";

        $user_id = $_SESSION['user']['id'];
        $mainModel = new MainModel();

        $countPass = $mainModel->getCountPasswordsByUser($user_id);
        $passwords = $mainModel->getPasswordsByUser($user_id);

        $this->render(
            "index",
            ['countPass' => $countPass, 'passwords' => $passwords]
        );
    }

    public function actionPassword() {
        if (empty($_GET['id'])) {
            $this->redirect('/');
        }

        $id = $_GET['id'];
        $mainModel = new MainModel();
        $data = $mainModel->getDataByPassword($id);

        if (empty($data)) {
            $this->redirect('/');
        }
        $this->view->title = $data['title'];
        $this->render("password", ['data' => $data]);
    }

    public function actionNew()
    {
        $this->view->title = "Новая запись";

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $mainModel = new MainModel();
            $result = $mainModel->new($_POST);
            if ($result) {
                $this->redirect("/");
            }
        }

        $this->render("new");
    }
}
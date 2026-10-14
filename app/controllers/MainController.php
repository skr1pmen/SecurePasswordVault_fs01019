<?php

namespace app\controllers;

use app\core\InitController;
use app\lib\Encrypted;
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
        $isShow = false;
        if (!empty($_GET['show'])) {
            if ($_GET['show'] === 'true') {
                $isShow = true;
            }
        }
        $data = $mainModel->getDataByPassword($id, $isShow);

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

    public function actionEdit() {
        if (empty($_GET['id'])) {
            $this->redirect('/');
        }

        $id = $_GET['id'];
        $mainModel = new MainModel();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $result = $mainModel->editPassword($id, $_POST);

            if ($result) {
                $this->redirect("/main/password?id=" . $id);
            }
        }

        $data = $mainModel->getDataByPassword($id, true);

        if (empty($data)) {
            $this->redirect('/');
        }

        $this->view->title = "Редактирование записи: ".$data['title'];
        $this->render("edit", ['data' => $data]);
    }

    public function actionDelete() {
        if (empty($_GET['id'])) {
            $this->redirect('/');
        }

        $id = $_GET['id'];
        $mainModel = new MainModel();
        $result = $mainModel->deletePassword($id);
        if (!empty($result)) {
            $this->redirect('/');
        }
        $_SESSION['error'] = 'Не удалось переместить запись в корзину';
        $this->redirect('/main/password?id='.$id);
    }

    public function actionBasket() {
        $this->view->title = "Корзина";

        $mainModel = new MainModel();

        if (empty($_GET['recovery']) || empty($_GET['delete'])) {
            $data = $mainModel->getBasketData($_SESSION['user']['id']);
            $this->render("basket", ['data' => $data]);
        }
        if (!empty($_GET['recovery'])) {
            $id = $_GET['recovery'];
            $mainModel->recoveryPassword($id);
            $this->redirect("/main/basket");
        }
        if (!empty($_GET['delete'])) {
            $id = $_GET['delete'];
            $mainModel->destructionPassword($id);
            $this->redirect("/main/basket");
        }
    }

    public function actionGeneration() {
        $this->view->title = "Генерация пароля";
        $password = [];

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $useUppercase = empty($_POST["uppercase"]) ? false : true;
            $useDigits = empty($_POST["digits"]) ? false : true;
            $useSymbol = empty($_POST["symbol"]) ? false : true;
            $passLength = $_POST["length"];
            $mainModel = new MainModel();
            $password = $mainModel->generatePassword($passLength, $useUppercase, $useDigits, $useSymbol);
        }

        $this->render("generation", ['password' => $password]);
    }

    public function actionExport() {
        $this->view->title = "Экспорт данных";

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $mainModel = new MainModel();
            $type = $_POST["type"];
            $password = trim($_POST['password']);
            $mainModel->exportPassword($type, $password);
        }
        $this->render("export");
    }
}
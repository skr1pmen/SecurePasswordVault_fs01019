<?php

namespace app\controllers;

use app\core\InitController;

class MainController extends InitController
{
    public function actionIndex() {
        $this->view->title = "Главная страница";
        $this->render("index");
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
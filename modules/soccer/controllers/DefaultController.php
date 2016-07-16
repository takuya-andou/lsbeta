<?php

namespace app\modules\soccer\controllers;

use yii\web\Controller;

class DefaultController extends Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionMatch($id)
    {
        return $this->render('index');
    }

}

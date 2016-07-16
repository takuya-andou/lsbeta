<?php

namespace app\modules\admin\controllers;

use Yii;
use yii\web\Controller;
/**
 * DefaultController
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class DefaultController extends Controller
{

    /**
     * Action index
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
}

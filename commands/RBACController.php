<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 02.07.2016
 * Time: 21:09
 */

namespace app\commands;
use yii\console\Controller;
use Yii;

class RbacController extends Controller
{
    public function actionInit(){
        $role = \Yii::$app->authManager->createRole('admin');
        $role->description = 'Администратор';
        Yii::$app->authManager->add($role);

        $role = \Yii::$app->authManager->createRole('user');
        $role->description = 'Пользователь';
        Yii::$app->authManager->add($role);
    }
}
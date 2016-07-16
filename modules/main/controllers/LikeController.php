<?php

namespace app\modules\main\controllers;

use yii\web\Controller;
use app\modules\main\models\Like;

class LikeController extends Controller
{
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
                //'like' => ['POST'],
                //'dislike' => ['POST'],
            ],
        ];
    }

    /**
     * @param $module_id
     * @param $item_id
     * @return array|bool
     */
    public function actionLike($module_id, $item_id)
    {
        $user_id = \Yii::$app->user->id;
        $ip = \Yii::$app->request->userIP;
        return Like::rateItem($module_id,$item_id,$user_id,$ip, Like::LIKE);
    }

    /**
     * @param $module_id
     * @param $item_id
     * @return array|bool
     */
    public function actionDislike($module_id, $item_id){
        $user_id = \Yii::$app->user->id;
        $ip = \Yii::$app->request->userIP;
        return Like::rateItem($module_id,$item_id,$user_id,$ip, Like::DISLIKE);
    }


}
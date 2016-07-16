<?php

namespace app\modules\betting\controllers;

use app\modules\betting\models\Bet;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;

/**
 * Default controller for the `betting` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionGetbetsbymatchid(){
        if(\Yii::$app->request->isAjax){
            $match_id = \Yii::$app->request->get('match_id');
            $bets = Bet::getBetsByMatchId($match_id);
            $bets_array = [];
            if(!empty($bets))
                $bets_array = ArrayHelper::map($bets, 'id', function($bet, $defaultValue){
                return $bet->toString();
            });
            echo json_encode($bets_array);
        }
        else{
            throw new ForbiddenHttpException();
        }
    }

    public function actionTest(){
        $arr = Bet::moveToHistory();

    }
}

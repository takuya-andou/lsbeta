<?php
namespace app\commands;

use app\extensions\geneticalgorithm\GAConfig;
use app\extensions\geneticalgorithm\GACore;
use app\modules\betting\models\Bet;
use Yii;
use app\modules\soccer\models;
use yii\base\Model;
use yii\console\Controller;
use yii\console\Exception;
use yii\helpers\Console;
use app\components\models\linear_v1\LinearModelController;

class ModelController extends Controller {


    public function actionGeneration() {
        $ga = new GACore([
            'debug' => true,
        ]);
        $ga->run();
    }

    public function actionTest(){
        $stats = models\MatchStats::getMatchStat(8,1);
        print_r($stats);
        //$result = LinearModelController::getInstance()->test($match_id);
        /*$tt = Bet::getLastBets(1,1, [
            'bets_num' => 120,
            'matches_num' => 2,
            /*'since_date' => '2016-05-06 14:43:40',
            'until_date' => '2016-05-12 14:43:40',*/
            //'upper_coef' => 3.5,
           /* 'bookie_ids' => [16]
        ]);
        foreach($tt as $t) echo $t->match_id.' ';*/
        //print_r($tt);
    }

    public function actionGa(){
        $ga_core = new GACore([
            'debug' => true,
        ]);
        $ga_core->run();
    }

}
<?php

namespace app\modules\soccer\controllers;

use app\modules\soccer\models\Match;
use app\modules\soccer\models\Team;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\data\Pagination;
use yii\web\ForbiddenHttpException;

class TeamController extends Controller {

    //soccer/team/24
    public function actionIndex($id=0) {
        if ($id>0 && $team = Team::getTeam($id)) {

            $matches = Team::getMatchesByTeamId($id);
            $goals = array();
            $possibleColors = array(
                'FFAA94',
                'FFE6B2',
                '8BBA8B',
                'EEEEEE'
            );
            foreach($matches as $v) {
                $goals[$v->id] = Match::getGoals($v->matchStats);
                //if draw or match not started
                if ($goals[$v->id]['home'] == $goals[$v->id]['away']) {
                    if ($goals[$v->id]['home'] == '?') {
                        $goals[$v->id]['color'] = $possibleColors[3];
                    } else {
                        $goals[$v->id]['color'] = $possibleColors[1];
                    }
                } elseif ($v->home->id == $id && $goals[$v->id]['home'] > $goals[$v->id]['away'] || $v->away->id == $id && $goals[$v->id]['home'] < $goals[$v->id]['away']) {
                    $goals[$v->id]['color'] = $possibleColors[2];
                } else {
                    $goals[$v->id]['color'] = $possibleColors[0];
                }

            }

            return $this->render('item', [
                'item' => $team,
                'matches' => $matches,
                'goals' => $goals
            ]);
        } else {
            return $this->render('index', []);
        }
    }

    public function actionGetteamsbypattern(){
        if(\Yii::$app->request->isAjax){
            $pattern = \Yii::$app->request->get('pattern');

            $teams = Team::getTeamsByPattern($pattern);
            echo json_encode(ArrayHelper::map($teams, 'id', 'name'));
        }
        else{
            throw new ForbiddenHttpException();
        }
    }
}